<?php
/**
 * @file
 * photos.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function photos_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'node-photo-field_categories'
  $field_instances['node-photo-field_categories'] = array(
    'bundle' => 'photo',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Add this photo to one or more categories to have it appear in that listing.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 1,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'fences_wrapper' => 'no_wrapper',
    'field_name' => 'field_categories',
    'label' => 'Categories',
    'required' => 0,
    'settings' => array(
      'behaviors' => array(
        'taxonomy-index' => array(
          'status' => TRUE,
        ),
      ),
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_buttons',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'node-photo-field_photo'
  $field_instances['node-photo-field_photo'] = array(
    'bundle' => 'photo',
    'deleted' => 0,
    'description' => 'Upload images with a height or width of 1600 pixels. The system will resize them as needed for use as thumbnails, etc.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'image_link' => '',
          'image_style' => 'photo_small',
        ),
        'type' => 'image',
        'weight' => 2,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'fences_wrapper' => 'figure',
    'field_name' => 'field_photo',
    'label' => 'photo',
    'required' => 0,
    'settings' => array(
      'alt_field' => 0,
      'default_image' => 0,
      'file_directory' => 'photos',
      'file_extensions' => 'png gif jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '1600x1600',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'photo_small',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 1,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Add this photo to one or more categories to have it appear in that listing.');
  t('Categories');
  t('Upload images with a height or width of 1600 pixels. The system will resize them as needed for use as thumbnails, etc.');
  t('photo');

  return $field_instances;
}
