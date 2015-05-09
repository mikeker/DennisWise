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
    dd('creating DropboxGallery');
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
   * Ensures this file has been included when unserializing this object.
   * Prevents __PHP_Incomplete_Class Object errors.
   */
  //public function __wakeup() {
  //  dd('in __wakeup for DropboxGallery');
  //  parent::__wakeup();
  //}

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
    $filesDir = drupal_realpath('public://') . '/drupal-image-gallery' . $gallery_name;
    foreach (array('', 'thumbnails', 'full') as $subDir) {
      $dir = $filesDir;
      if (!empty($subDir)) {
        $dir = "$filesDir/$subDir";
      }
      if (!file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
        $this->error = t('Unable to create %dir', array('%dir' => $filesDir));
        return FALSE;
      }
    }
    return $filesDir;
  }

  /**
   * Deletes all lcoal copies of a given gallery's images.
   *
   * @param $gallery_name
   *   Gallery name (folder name) from Dropbox.
   *
   * @return bool
   */
  public function deleteGalleryDirectories($gallery_name) {
    $filesDir = drupal_realpath('public://') . '/drupal-image-gallery' . $gallery_name;
    return file_unmanaged_delete_recursive($filesDir);
  }

  /**
   * Builds or refreshes the full/thumbnail images.
   *
   * @param $gallery
   *
   * @return bool
   *   TRUE on success, FALSE otherwise.
   */
  public function refresh($gallery) {
    $folder = $this->getFolderMeta($gallery);
    if (empty($folder)) {
      $this->error = t('Could not find a Dropbox folder associated with %gallery', array('%gallery' => $gallery));
      return FALSE;
    }

    // List all the files in this folder.
    $files = $this->getClient()->getMetadataWithChildren($folder['path']);
    if (empty($files['contents'])) {
      $this->error = t('There are no photos in this image gallery.');
      return FALSE;
    }

    // Limit the list to photos.
    $photos = array();
    foreach ($files['contents'] as $file) {
      if ('image/jpeg' == $file['mime_type']) {
        $photos[] = $file;
      }
    }

    $this->deleteGalleryDirectories($folder['path']);
    if (!$filesDir = $this->prepareDirectories($folder['path'])) {
      // $this->error is set by prepareDirectory.
      return FALSE;
    }

    // Download each original, resize for "full" and "thumbnail" via Image
    // Styles, then delete the originals from our server.
    // @TODO: need to deal with changes to originals on Dropbox.
    // @TODO: handle difference between Windows and Linux filenames.
    //$operations = array();
    //$total = count($photos);
    //foreach ($photos as $index => $photo) {
    //  $index++;   // Arrays are zero-indexed.
    //  $operations[] = array(
    //    'dropbox_gallery_batch_refresh_file',
    //    array(
    //      $filesDir,
    //      $photo,
    //      t('Processing image @name: @curr out of @total', array(
    //        '@name' => Dropbox\Path::getName($photo['path']),
    //        '@curr' => $index,
    //        '@total' => $total,
    //      )),
    //      $index / $total,
    //    ),
    //  );
    //}

    // Create derivatives as a batch operation and return to the admin page.
    batch_set(array(
      'operations' => array(
        'dropbox_gallery_batch_refresh_file',
        array(
          $filesDir,
          $photos,
        )
      ),
      'finished' => 'dropbox_gallery_batch_refresh_finished',
    ));
    batch_process('admin/config/media/dropbox_gallery');
  }

  /**
   * Used by the Batch API to download create derivatives of a single file.
   *
   * @param $filesDir
   *   String containing the path to the main gallery folder. E.g.:
   *   /var/www/sites/default/files/drupal-image-gallery/gallery-name
   * @param $photo
   *   Dropbox file metadata array.
   *
   * @see https://www.dropbox.com/developers/core/docs#metadata-details
   */
  public function refreshSingle($filesDir, $photo) {
    $baseFilename = Dropbox\Path::getName($photo['path']);
    $originalPath = "$filesDir/$baseFilename";
    if ($f = fopen($originalPath, 'w')) {
      $folder = $this->getClient()->getFile($photo['path'], $f);
      fclose($f);
    }
    else {
      $this->error = t('Unable to open %file', array('%file' => $originalPath));
      return FALSE;
    }
    $imagesMeta[$baseFilename] = $folder;

    // Generate thumbnail and "full"-sized derivatives. (The full-sized image
    // saved here is not the full-sized image from Dropbox, but rather a down-
    // rez'ed image that is shown when the visitor clicks on a thumbnail.
    // Unfortunate terminology...
    // NOTE: Generate the large image first, if there is an error then the
    // thumbnail will not be generated, reducing our chances of 404's when
    // clicking through to the larger version.
    // @TODO: configurable sizing options and folder names.
    if (!image_style_create_derivative('photo_large', $originalPath, $filesDir . '/full/' . $baseFilename) ||
      !image_style_create_derivative('photo_small', $originalPath, $filesDir . '/thumbnails/' . $baseFilename)) {
      $this->error .= 'There was an error generating derivatives for ' . $baseFilename . '.<br />';
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
