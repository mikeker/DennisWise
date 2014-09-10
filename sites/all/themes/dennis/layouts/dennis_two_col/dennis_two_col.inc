<?php

/**
 * Defines a two column layout for use on DennisWise.com.
 */
$plugin = array(
  'title' => t('Two col: fixed/fluid'),
  'icon' => 'dennis_two_col.png',
  'category' => t('Dennis'),
  'theme' => 'dennis-two-col',
  'admin css' => 'dennis-two-col-admin.css',
  'regions' => array(
    'fixed' => t('Fixed column'),
    'fluid' => t('Fluid column'),
  ),
);
