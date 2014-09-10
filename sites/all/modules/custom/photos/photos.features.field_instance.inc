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

  // Exported field_instance: 'node-photo-field_photo'
  $field_instances['node-photo-field_photo'] = array(
    'bundle' => 'photo',
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'file',
        'settings' => array(),
        'type' => 'file_default',
        'weight' => 0,
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
    'field_name' => 'field_photo',
    'label' => 'Photo',
    'required' => 0,
    'settings' => array(
      'description_field' => 0,
      'file_directory' => 'photos',
      'file_extensions' => 'jpg jpeg png',
      'max_filesize' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'file',
      'settings' => array(
        'progress_indicator' => 'throbber',
      ),
      'type' => 'file_generic',
      'weight' => -4,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Photo');

  return $field_instances;
}
