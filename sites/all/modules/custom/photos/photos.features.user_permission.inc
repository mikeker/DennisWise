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

  // Exported permission: 'administer FTP galleries'.
  $permissions['administer FTP galleries'] = array(
    'name' => 'administer FTP galleries',
    'roles' => array(
      'administrator' => 'administrator',
    ),
    'module' => 'ftp_gallery',
  );

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

  // Exported permission: 'view FTP galleries'.
  $permissions['view FTP galleries'] = array(
    'name' => 'view FTP galleries',
    'roles' => array(
      'anonymous user' => 'anonymous user',
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'ftp_gallery',
  );

  return $permissions;
}
