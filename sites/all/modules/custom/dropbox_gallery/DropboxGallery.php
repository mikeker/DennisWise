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
   * @var string
   */
  protected $error;

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

    $this->error = '';
  }

  /**
   * Returns the most recent error.
   *
   * @return string
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Returns an array of all folders in the user's Dropbox App directory.
   * Return array is in the form of 'path' => metadata.
   *
   * @return array
   *
   * @see https://www.dropbox.com/developers/core/docs#metadata-details
   */
  public function getAllFolders() {
    $rootFolder = $this->getClient()->getMetadataWithChildren('/');
    $folders = array();
    foreach ($rootFolder['contents'] as $folder) {
      if ($folder['is_dir']) {
        $path = ltrim($folder['path'], '/');
        $folders[$path] = $folder;
      }
    };
    return $folders;
  }

  /**
   * Returns a Dropbox folder's metadata for a given gallery name.
   *
   * @param $gallery_name
   *   String with the gallery name, which matches a subfolder in Dropbox
   *   containing the images in this gallery.
   *
   * @return metadata|null
   *
   * @see https://www.dropbox.com/developers/core/docs#metadata-details
   */
  public function getFolderMeta($gallery_name) {
    // Collect all the folders under the App folder and check if a gallery is
    // named the same. Allow some mismatching for misc characters in names.
    $folder = NULL;
    foreach ($this->getAllFolders() as $path => $folder_metadata) {
      if ($gallery_name == $path || str_replace(array('-', '_', '+'), array(' ', ' ', ' '), $gallery_name) == $path) {
        $folder = $folder_metadata;
        break;
      }
    }

    return $folder;
  }

  /**
   * Returns the full path on the local server to the files containing the
   * specified gallery, without any trailing slash.
   *
   * @param $gallery_name
   *
   * @return string
   */
  protected function getGalleryDirectory($gallery_name) {
    trim($gallery_name, '/');
    return drupal_realpath('public://') . '/dropbox-image-gallery/' . $this->drupalUser . '/' . $gallery_name;
  }

  /**
   * Returns an array of full paths to the derivative image directories.
   *
   * @param $gallery_name
   *
   * @return array
   */
  public function getDerivativeDirectories($gallery_name) {
    $root = $this->getGalleryDirectory($gallery_name);
    return array(
      'thumbnails' => $root . '/thumbnails',
      'full' => $root . '/full',
    );
  }

  /**
   * Returns an array of image styles associated with each derivative name.
   *
   * @return array
   *
   * @TODO: Make this admin configurable.
   */
  protected function getDerivativeStyles() {
    return array(
      'thumbnails' => 'photo_small',
      'full' => 'photo_large',
    );
  }

  /**
   * Build the local gallery directories if they don't already exist.
   *
   * @param $gallery_name
   *   Gallery name (folder name) from Dropbox.
   *
   * @return string
   *   Path on the local server to the gallery root directory.
   *
   * @TODO: Deal with out-of-bounds characters and Win/Linux/Mac naming issues.
   */
  public function prepareDirectories($gallery_name) {
    $root = $this->getGalleryDirectory($gallery_name);
    dd("preparing directories for $root...");

    foreach (array($root) + $this->getDerivativeDirectories($gallery_name) as $dir) {
      dd("making $dir");
      if (!file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
        $this->error = t('Unable to create %dir', array('%dir' => $dir));
        return FALSE;
      }
    }

    return $root;
  }

  /**
   * Deletes all local copies of a given gallery's images.
   *
   * @param $gallery_name
   *   Gallery name (folder name) from Dropbox.
   *
   * @return bool
   */
  public function deleteGalleryDirectories($gallery_name) {
    $filesDir = $this->getGalleryDirectory($gallery_name);
    return file_unmanaged_delete_recursive($filesDir);
  }

  /**
   * Builds or refreshes the full/thumbnail images.
   *
   * @param $gallery_name
   *
   * @return bool
   *   TRUE on success, FALSE otherwise.
   */
  public function refresh($gallery_name) {
    $folder = $this->getFolderMeta($gallery_name);
    if (empty($folder)) {
      $this->error = t('Could not find a Dropbox folder associated with %gallery', array('%gallery' => $gallery_name));
      return FALSE;
    }

    // List all the files in this folder.
    $files = $this->getClient()->getMetadataWithChildren($folder['path']);
    if (empty($files['contents'])) {
      $this->error = t('There are no photos in this image gallery.');
      return FALSE;
    }

    // Limit the list to JPEG photos.
    $photos = array();
    foreach ($files['contents'] as $file) {
      if ('image/jpeg' == $file['mime_type']) {
        $photos[] = $file;
      }
    }
    if (empty($photos)) {
      $this->error = t('There are no photos in this image gallery. Dropbox Gallery only works with JPEG images.');
      return FALSE;
    }

    $this->deleteGalleryDirectories($gallery_name);
    if (!$this->prepareDirectories($gallery_name)) {
      // $this->error is set by prepareDirectory.
      return FALSE;
    }

    // Create derivatives as a batch operation and return to the admin page.
    // @TODO: handle difference between Windows and Linux filenames.
    batch_set(array(
      'operations' => array(
        array('dropbox_gallery_batch_refresh_file', array($gallery_name, $photos))
      ),
      'progress_message' => t('Creating local copies of the photos in %gallery', array('%gallery' => $gallery_name)),
      'finished' => 'dropbox_gallery_batch_refresh_finished',
    ));
    batch_process('admin/config/media/dropbox_gallery');
  }

  /**
   * Used by the Batch API to download and create derivatives of a single file.
   *
   * @param $gallery_name
   *   String containing the path to the main gallery folder. E.g.:
   *   /var/www/sites/default/files/drupal-image-gallery/uid/gallery-name
   * @param $photo
   *   Dropbox file metadata array.
   *
   * @see https://www.dropbox.com/developers/core/docs#metadata-details
   */
  public function refreshSingle($gallery_name, $photo) {
    $baseFilename = Dropbox\Path::getName($photo['path']);
    $originalPath = $this->getGalleryDirectory($gallery_name) . '/' . $baseFilename;
    dd("downloading to $originalPath");
    if ($f = fopen($originalPath, 'w')) {
      $folder = $this->getClient()->getFile($photo['path'], $f);
      fclose($f);
    }
    else {
      dd('unable to open file');
      $this->error = t('Unable to open %file', array('%file' => $originalPath));
      return FALSE;
    }

    // Generate derivative images based on what is defined by
    // getDerivativeStyles(). This allows for easy expansion in case we want
    // more than just thumbnails and full-sized images.
    $styles = $this->getDerivativeStyles();
    foreach($this->getDerivativeDirectories($gallery_name) as $derivative => $dir) {
      dd("Generating style for $derivative: " . $styles[$derivative] . " to $dir/$baseFilename");
      if (!image_style_create_derivative($styles[$derivative], $originalPath, "$dir/$baseFilename")) {
        dd('error');
        $this->error = t('There was an error generating the %style derivative for %file.<br />', array(
          '%style' => $styles[$derivative],
          '%file' => $baseFilename,
        ));
        return FALSE;
      }
    }

    // Delete the original downloaded from Dropbox -- we only care about the
    // derivatives for setting up the gallery.
    if (!file_unmanaged_delete($originalPath)) {
      // Non-fatal error so continue but not in the log that the original was
      // unable to be deleted.
      watchdog('Dropbox Gallery', 'Unable to delete the downloaded file: %file', array('%file' => $originalPath));
    }

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
   *
   * @TODO: Refactor to return just the images array -- have the module file
   * pass that along to a theme function/template.
   *
   * @TODO: How to handle when user deletes the Dropbox folder. Do we still show
   * the gallery as long as we have the local derivative files? Currently we do
   * not...
   */
  public function view($uid, $gallery) {
    if (is_object($uid)) {
      $uid = $uid->uid;
    }
    if (!$uid) {
      // Anonymous users can't have galleries.
      drupal_not_found();
    }

    $gallery = _dropbox_gallery_get_gallery_object($uid);
    $folder = $gallery->getFolderMeta($gallery);
    if (empty($folder)) {
      drupal_set_message(t('The specified gallery %gallery cannot be found', array('%gallery' => $gallery)));
      drupal_not_found();
    }

    dpr($folder);
    exit;


    $output = '<ul><li>';
    $output .= join('</li><li>', $items);
    $output .= '</li></ul>';

    return $output;
  }

}
