<?php
/******************************************************************************
 *                          Admin Pages
 *****************************************************************************/

/**
 * Main FTP Gallery administration form.
 */
function ftp_gallery_admin_form($form_state) {
  $form = array();

  // Allow users to specify the base URL where galleries will appear
  global $base_url;
  $form['ftp_gallery_path'] = array(
    '#type' => 'textfield',
    '#title' => t('URL for galleries'),
    '#description' => t(
      'Enter the root URL you want for FTP Galleries. Do not include a leading
      or trailing slash. For example, enter <em>gallery/photos</em> to have your
      galleries appear at the URL %url',
      array('%url' => "$base_url/gallery/photos/gallery-name")
    ),
    '#default_value' => variable_get('ftp_gallery_path', 'gallery'),
    '#field_prefix' => "$base_url/",
    '#required' => TRUE,
  );

  // Allow users to specify the directory photos will be stored
  $files_dir = variable_get('file_public_path', conf_path() . '/files');
  $form['ftp_gallery_directory'] = array(
    '#type' => 'textfield',
    '#title' => t('Root directory for storing photos'),
    '#description' => t(
      'Enter the root directory for uploading photos via FTP. Do not include a leading
      or trailing slash. New galleries will be created as subdirectories of
      the directory specified here. For example, enter <em>photos/galleries</em>
      to upload files to %dir.',
      array('%dir' => "$files_dir/photos/galleries/gallery_name")
    ),
    '#default_value' => variable_get('ftp_gallery_directory', 'photos/galleries'),
    '#field_prefix' => "$files_dir/",
  );

  // Default theme links a thumbnail to the full-sized version. Let the users
  // select the image style preset for each.
  $styles = image_styles();
  $options = array();
  foreach ($styles as $id => $preset) {
    $options[$id] = $preset['label'];
  }
  $form['ftp_gallery_imagestyle'] = array(
    '#markup' => '<p>'
      . t('FTP Gallery\'s default theme creates a link from a thumbnail image to
        a full sized image, allowing you to adjust the display using CSS or a
        third-party image overlay such as Lightbox or Shadowbox.')
      . '</p>',
  );
  $form['ftp_gallery_imagestyle_thumb'] = array(
    '#type' => 'select',
    '#title' => t('Imagecache preset for thumbnails'),
    '#options' => $options,
    '#default_value' => variable_get('ftp_gallery_imagecache_thumb', ''),
  );
  $form['ftp_gallery_imagestyle_full'] = array(
    '#type' => 'select',
    '#title' => t('Imagecache preset for full-sized images'),
    '#options' => $options,
    '#default_value' => variable_get('ftp_gallery_imagecache_full', ''),
  );

  // Build a table to show all galleries
  $header = array(
    t('Gallery Title'),
    t('Directory'),
    t('Operations')
  );
  $rows = array();
  $galleries = _ftp_gallery_get_galleries();
  $gallery_path = variable_get('ftp_gallery_path', 'gallery');
  foreach ($galleries as $name => $gallery) {
    $row = array();
    $row[] = $name;
    $row[] = $gallery['directory'];
    $row[] = l(t('View'), "$gallery_path/" . $gallery['path'], array('alias' => TRUE))
      .' | '. l(t('Delete'), 'admin/config/media/ftp_gallery/' . $gallery['path'] . '/delete', array('alias' => TRUE));
    $rows[] = $row;
  }
  $form['existing'] = array(
    '#markup' => theme('table', array('header' => $header, 'rows' => $rows))
  );

  $form['add_new'] = array(
    '#markup' => '<p>' . l(t('Add a new gallery'), 'admin/config/media/ftp_gallery/add') . '</p>',
  );

  $form['#submit'][] = 'ftp_gallery_admin_form_submit';
  return system_settings_form($form);
}

function ftp_gallery_admin_form_submit($form, &$form_state) {
  // Update the ftp_gallery_path now so that when we flush caches, it gets
  // the correct info when rebuilding the menu structure
  variable_set('ftp_gallery_path', $form_state['values']['ftp_gallery_path']);

  // May need to rebuild menu if the user changed the gallery_path.
  menu_rebuild();
}

/**
 * Add new gallery form
 */
function ftp_gallery_add_gallery_form($form_state) {
  $form = array();

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Gallery name'),
    '#description' => t('Use only letters, numbers and spaces.  No hyphens, underscores or special characters.')
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Add Gallery',
  );

  return $form;
}

/**
 * Add new gallery form validation
 */
function ftp_gallery_add_gallery_form_validate ($form, &$form_state) {
  $name = $form_state['values']['name'];
  if (empty($name)) {
    form_set_error('add_name', t('A gallery name is required'));
  }

  $galleries = _ftp_gallery_get_galleries();
  if (isset($galleries[$name])) {
    form_set_error('add_name', t('A gallery with that name already exists'));
  }

  if (!preg_match('/^[a-zA-Z0-9 ]+$/', $name)) {
    form_set_error('add_name', t('Use only letters, numbers and spaces.  No hyphens, underscores or special characters.'));
  }
}

/**
 * Add new gallery form submit function
 */
function ftp_gallery_add_gallery_form_submit ($form, &$form_state) {
  $name = $form_state['values']['name'];
  $galleries = _ftp_gallery_get_galleries();
  global $base_url;

  $gallery = array(
    'directory' => _ftp_gallery_name_to_dir($name),
    'path' => _ftp_gallery_name_to_url($name),
    'uri' => _ftp_gallery_name_to_uri($name),
  );

  // Create directory
  if (is_dir($gallery['directory'])) {
    drupal_set_message(t(
      'The directory %dir already exists. Photos located in that directory will
      be used for the new gallery.',
      array('%dir' => $gallery['directory'])
    ));
  }
  else {
    if (@mkdir($gallery['directory'], 0775, TRUE)) {
      drupal_set_message(t(
        'Created the directory %dir. FTP photos into that directory to populate
        this gallery',
        array('%dir' => $gallery['directory'])
      ));
    }
    else {
      drupal_set_message(t(
        'Unable to create the directory %dir. Make sure the permissions and
        ownership are set correctly on the parent directory',
        array('%dir' => $gallery['directory'])
      ), 'error');
      return;
    }
  }

  // Save new gallery
  $galleries[$name] = $gallery;
  variable_set('ftp_galleries', $galleries);
}

/**
 * Delete gallery form
 */
function ftp_gallery_delete_form ($form_state, $name) {
  $form = array();
  $name = _ftp_gallery_url_to_name($name);

  // Get Gallery info
  $galleries = _ftp_gallery_get_galleries();
  if (!isset($galleries[$name])) {
    drupal_set_message(t(
      'An invalid gallery was specified: %gallery',
      array('%gallery' => $name)
    ));
    return;
  }

  $form['name'] = array(
    '#type' => 'value',
    '#value' => $name,
  );

  $form['delete_files'] = array(
    '#type' => 'checkbox',
    '#title' => t('Delete all files associated with this gallery'),
    '#default_value' => FALSE,
    '#description' => t('All files in %dir will be deleted.  This cannot be undone.',
      array('%dir' => $galleries[$name]['directory'])),
  );

  return confirm_form(
    $form,
    t('Are you sure you want to delete the %name gallery', array('%name' => $name)),
    'admin/config/media/ftp_gallery/admin',
    '',
    t('Delete Gallery')
  );
}

/**
 * Delete gallery submit handler
 *
 * Deletes an FTP Gallery from the system and, optionally, all files assoicated
 * with it.
 */
function ftp_gallery_delete_form_submit ($form, &$form_state) {
  // Return to the admin page when done.
  $form_state['redirect'] = 'admin/config/media/ftp_gallery/admin';

  $name = $form_state['values']['name'];
  $galleries = _ftp_gallery_get_galleries();
  if (empty($galleries[$name])) {
    drupal_set_message(t('Invalid gallery specified: %name', array('%name' => $name)));
    return;
  }
  $gallery = $galleries[$name];

  // Delete gallery from the list of all galleries
  unset($galleries[$name]);
  variable_set('ftp_galleries', $galleries);
  drupal_set_message(t('The FTP Gallery %name has been deleted.', array('%name' => $name)));

  // Delete files and directory associated with this gallery
  if ($form_state['values']['delete_files']) {
    $files = file_scan_directory($gallery['directory'], '.*');
    foreach ($files as $file) {
      file_delete($file->filename);
    }
    if (rmdir($gallery['directory'])) {
      drupal_set_message(t(
        'All files in %dir have been deleted and that directory has been removed.',
        array('%dir' => $gallery['directory'])
      ));
    }
    else {
      drupal_set_message(t(
        'There was an error removing the directory %dir. You will need to remove
        the files and delete the directory manually.',
        array('%dir' => $gallery['directory'])
      ));
    }
  }
}
