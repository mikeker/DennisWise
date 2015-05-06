<?php

/**
 * @file
 * Provides the implementation of a Dropbox Gallery.
 */
namespace DropboxGallery;

use DropboxApp;
use Dropbox;

class DropboxGallery extends DropboxApp {
  /**
   * @var \DropboxApp
   */
  protected $app;

  /**
   * Instatiates a Dropbox Gallery owned by a given Drupal user.
   *
   * @param $uid
   *   UID or user object of the gallery owner. They must have authorized the
   *   Dropbox app with their Dropbox account.
   */
  public function __construct($uid = '') {
    if (is_object($uid)) {
      $uid = $uid->uid;
    }
    if (empty($uid)) {
      global $user;
      $uid = $user->uid;
    }

    parent::__construct('Drupal-Photo-Gallery/1.0', 'https://vagrant.denniswise.com/dropbox_gallery/authorize/finish');
    if (!$this->setDrupalUser($uid)) {
      // The specified user doesn't have an authorization token from Dropbox.
      drupal_not_found();
    }
  }

  /**
   * Returns a Dropbox folder's metadata for a given gallery name.
   *
   * @param $gallery
   *   String with the gallery name, which matches a subfolder in Dropbox
   *   containing the images in this gallery.
   *
   * @return metadata|null
   *
   * @see https://www.dropbox.com/developers/core/docs#metadata-details
   */
  public function getFolderMeta($gallery) {
    // Collect all the folders under the App folder and check if a gallery is
    // named the same. Allow some mismatching for misc characters in folder names.
    dpr($this);
    $rootFolder = $this->getClient()->getMetadataWithChildren('/');
    $folder = NULL;

    foreach ($rootFolder['contents'] as $item) {
      if (!$item['is_dir']) {
        continue;
      }

      $path = ltrim($item['path'], '/');
      if ($gallery == $path || str_replace(array('-', '_', '+'), array(' ', ' ', ' '), $gallery) == $path) {
        $folder = $item;
        break;
      }
    }

    return $folder;
  }

  /**
   * Builds or refreshes the full/thumbnail images.
   *
   * @param $gallery
   * @param &$error
   *   Contains details about errors.
   *
   * @return bool
   *   TRUE on success, FALSE otherwise.
   */
  public function refresh($gallery, &$error = '') {
    $meta = $this->getFolderMeta($gallery);
    if (empty($meta)) {
      return FALSE;
    }

    // List all the files in this folder.
    $files = $this->getClient()->getMetadataWithChildren($folder['path']);
    if (empty($files['contents'])) {
      $error = t('There are no photos in this image gallery.');
      return FALSE;
    }

    // Limit the list to photos.
    $photos = array();
    foreach ($files['contents'] as $file) {
      if ('image/jpeg' == $file['mime_type']) {
        $photos[] = $file;
      }
    }

    // Build a drupal-image-gallery directory if it doesn't exist.
    $filesDir = drupal_realpath('public://') . '/drupal-image-gallery';
    if (!is_dir($filesDir)) {
      file_prepare_directory($filesDir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    }

    dpr($photos); exit;
    // Download each original, resize for "full" and "thumbnail" via Image
    // Styles, then delete the originals from our server.
    // @TODO: need to deal with changes to originals on Dropbox.
    $imagesMeta = array();
    foreach ($photos as $photo) {
      // @TODO: handle difference between Windows and Linux filenames.
      $baseFilename = Dropbox\Path::getName($photo['path']);
      $f = fopen("$filesDir/$baseFilename", 'w');
      $meta = $this->getClient()->getFile($photo['path'], $f);
      fclose($f);
      $imagesMeta[$baseFilename] = $meta;
    }
    dpr($imagesMeta);
    exit;

    return TRUE;
  }

  /**
   * Displays a Dropbox gallery owned by a specied user.
   *
   * @param $uid
   *   UID or user object of the gallery owner. They must have authorized the
   *   Dropbox app with their Dropbox account.
   * @param $gallery
   *
   * @return string
   *   HTML
   */
  public function view($uid, $gallery) {
    if (is_object($uid)) {
      $uid = $uid->uid;
    }
    if (!$uid) {
      // Anonymous users can't have galleries.
      drupal_not_found();
    }

    $folder = dropbox_gallery_get_folder($gallery);
    if (empty($folder)) {
      drupal_set_message(t('The specified gallery %gallery cannot be found', array('%gallery' => $gallery)));
      drupal_not_found();
    }


    $output = '<ul><li>';
    $output .= join('</li><li>', $items);
    $output .= '</li></ul>';

    return $output;
  }

}
