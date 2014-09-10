<?php
/**
 * @file
 * photos.features.user_permission.inc
 */

/**
 * Implements hook_user_default_permissions().
 */
function photos_user_default_permissions() {
  $permissions = array();

  // Exported permission: 'create photo content'.
  $permissions['create photo content'] = array(
    'name' => 'create photo content',
    'roles' => array(),
    'module' => 'node',
  );

  // Exported permission: 'delete any photo content'.
  $permissions['delete any photo content'] = array(
    'name' => 'delete any photo content',
    'roles' => array(),
    'module' => 'node',
  );

  // Exported permission: 'delete own photo content'.
  $permissions['delete own photo content'] = array(
    'name' => 'delete own photo content',
    'roles' => array(),
    'module' => 'node',
  );

  // Exported permission: 'delete terms in categories'.
  $permissions['delete terms in categories'] = array(
    'name' => 'delete terms in categories',
    'roles' => array(),
    'module' => 'taxonomy',
  );

  // Exported permission: 'edit any photo content'.
  $permissions['edit any photo content'] = array(
    'name' => 'edit any photo content',
    'roles' => array(),
    'module' => 'node',
  );

  // Exported permission: 'edit own photo content'.
  $permissions['edit own photo content'] = array(
    'name' => 'edit own photo content',
    'roles' => array(),
    'module' => 'node',
  );

  // Exported permission: 'edit terms in categories'.
  $permissions['edit terms in categories'] = array(
    'name' => 'edit terms in categories',
    'roles' => array(),
    'module' => 'taxonomy',
  );

  return $permissions;
}
