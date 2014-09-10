<?php
/**
 * @file
 * photos.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function photos_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'additional_settings__active_tab_photo';
  $strongarm->value = 'edit-menu';
  $export['additional_settings__active_tab_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_image_wrap';
  $strongarm->value = 0;
  $export['borealis_image_wrap'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_responsive_builds';
  $strongarm->value = array(
    'respondsmall' => 250,
    'respondmedium' => 437.5,
    'respondlarge' => 765.625,
    'respondxl' => 1339.84375,
    'respondxl2' => 2344.7265625,
    'respondxl3' => 4103.271484375,
    'respondxl4' => 7180.7250976562,
  );
  $export['borealis_responsive_builds'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_ri_children';
  $strongarm->value = array(
    0 => 'large_responsive',
    1 => 'small_responsive',
  );
  $export['borealis_ri_children'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_ri_hidpi';
  $strongarm->value = '1.75';
  $export['borealis_ri_hidpi'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_ri_lazyload';
  $strongarm->value = '0';
  $export['borealis_ri_lazyload'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_ri_min_size';
  $strongarm->value = '250';
  $export['borealis_ri_min_size'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'borealis_ri_parents';
  $strongarm->value = array(
    'large_responsive' => array(
      'isid' => '7',
      'name' => 'large_responsive',
      'label' => 'Large (responsive)',
      'module' => NULL,
      'storage' => 1,
      'effects' => array(
        7 => array(
          'label' => 'Scale',
          'help' => 'Scaling will maintain the aspect-ratio of the original image. If only a single dimension is specified, the other dimension will be calculated.',
          'effect callback' => 'image_scale_effect',
          'dimensions callback' => 'image_scale_dimensions',
          'form callback' => 'image_scale_form',
          'summary theme' => 'image_scale_summary',
          'module' => 'image',
          'name' => 'image_scale',
          'data' => array(
            'width' => '1600',
            'height' => '1600',
            'upscale' => 0,
          ),
          'ieid' => '7',
          'isid' => '7',
          'weight' => '1',
        ),
      ),
    ),
    'small_responsive' => array(
      'isid' => '6',
      'name' => 'small_responsive',
      'label' => 'Small (responsive)',
      'module' => NULL,
      'storage' => 1,
      'effects' => array(
        6 => array(
          'label' => 'Scale',
          'help' => 'Scaling will maintain the aspect-ratio of the original image. If only a single dimension is specified, the other dimension will be calculated.',
          'effect callback' => 'image_scale_effect',
          'dimensions callback' => 'image_scale_dimensions',
          'form callback' => 'image_scale_form',
          'summary theme' => 'image_scale_summary',
          'module' => 'image',
          'name' => 'image_scale',
          'data' => array(
            'width' => '250',
            'height' => '250',
            'upscale' => 0,
          ),
          'ieid' => '6',
          'isid' => '6',
          'weight' => '1',
        ),
      ),
    ),
  );
  $export['borealis_ri_parents'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_anonymous_photo';
  $strongarm->value = 0;
  $export['comment_anonymous_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_default_mode_photo';
  $strongarm->value = 1;
  $export['comment_default_mode_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_default_per_page_photo';
  $strongarm->value = '50';
  $export['comment_default_per_page_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_form_location_photo';
  $strongarm->value = 1;
  $export['comment_form_location_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_photo';
  $strongarm->value = '0';
  $export['comment_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_preview_photo';
  $strongarm->value = '1';
  $export['comment_preview_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'comment_subject_field_photo';
  $strongarm->value = 1;
  $export['comment_subject_field_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'field_bundle_settings_node__photo';
  $strongarm->value = array(
    'view_modes' => array(),
    'extra_fields' => array(
      'form' => array(
        'title' => array(
          'weight' => '0',
        ),
        'path' => array(
          'weight' => '3',
        ),
      ),
      'display' => array(),
    ),
  );
  $export['field_bundle_settings_node__photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'menu_options_photo';
  $strongarm->value = array();
  $export['menu_options_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'menu_parent_photo';
  $strongarm->value = 'main-menu:0';
  $export['menu_parent_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'node_options_photo';
  $strongarm->value = array(
    0 => 'status',
  );
  $export['node_options_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'node_preview_photo';
  $strongarm->value = '0';
  $export['node_preview_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'node_submitted_photo';
  $strongarm->value = 0;
  $export['node_submitted_photo'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_node_photo_pattern';
  $strongarm->value = '';
  $export['pathauto_node_photo_pattern'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'save_continue_photo';
  $strongarm->value = 'Save and add fields';
  $export['save_continue_photo'] = $strongarm;

  return $export;
}
