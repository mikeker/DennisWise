<?php
/**
 * @file
 * photos.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_handlers().
 */
function photos_default_page_manager_handlers() {
  $export = array();

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'term_view_panel_context';
  $handler->task = 'term_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Photo category display',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'term_vocabulary',
          'settings' => array(
            'machine_name' => array(
              'categories' => 'categories',
            ),
          ),
          'context' => 'argument_term_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'dennis_two_col';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'left' => NULL,
      'right' => NULL,
      'fixed' => NULL,
      'fluid' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '143b6a70-caac-4ef6-9298-e5d1bc62dc0b';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-75ed57be-a8ce-4ad3-8c50-a50bad64fd59';
    $pane->panel = 'fixed';
    $pane->type = 'block';
    $pane->subtype = 'system-main-menu';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 1,
      'override_title_text' => 'DennisWise',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '75ed57be-a8ce-4ad3-8c50-a50bad64fd59';
    $display->content['new-75ed57be-a8ce-4ad3-8c50-a50bad64fd59'] = $pane;
    $display->panels['fixed'][0] = 'new-75ed57be-a8ce-4ad3-8c50-a50bad64fd59';
    $pane = new stdClass();
    $pane->pid = 'new-6bb51a0f-f7d4-4386-af40-ba84d0a0c6cb';
    $pane->panel = 'fluid';
    $pane->type = 'views_panes';
    $pane->subtype = 'photos_by_term-panel_pane_1';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'context' => array(
        0 => 'argument_term_1',
      ),
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array(
      'css_id' => '',
      'css_class' => 'photos-listing',
    );
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '6bb51a0f-f7d4-4386-af40-ba84d0a0c6cb';
    $display->content['new-6bb51a0f-f7d4-4386-af40-ba84d0a0c6cb'] = $pane;
    $display->panels['fluid'][0] = 'new-6bb51a0f-f7d4-4386-af40-ba84d0a0c6cb';
    $pane = new stdClass();
    $pane->pid = 'new-55e648db-7374-41fd-86b0-48185d1939ff';
    $pane->panel = 'fluid';
    $pane->type = 'page_messages';
    $pane->subtype = 'page_messages';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array();
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '55e648db-7374-41fd-86b0-48185d1939ff';
    $display->content['new-55e648db-7374-41fd-86b0-48185d1939ff'] = $pane;
    $display->panels['fluid'][1] = 'new-55e648db-7374-41fd-86b0-48185d1939ff';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $export['term_view_panel_context'] = $handler;

  return $export;
}
