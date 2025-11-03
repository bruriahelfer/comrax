<?php

/**
 * @file
 * Contains database additions to search-api-db-base.php.
 *
 * Can be used for setting up a base Search API sorts installation.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions, ['allowed_classes' => FALSE]);
$extensions['module']['menu_block'] = 0;
$extensions['module']['menu_multilingual'] = 0;
$connection->update('config')
  ->fields([
    'data' => serialize($extensions),
  ])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();

$connection->insert('config')
  ->fields(['name', 'data', 'collection'])
  ->values([
    'name' => 'block.block.mainnavigation',
    'data' => 'a:12:{s:4:"uuid";s:36:"461cc0d7-0ae7-4f6a-b06c-82be444bc56b";s:8:"langcode";s:2:"en";s:6:"status";b:1;s:12:"dependencies";a:3:{s:6:"config";a:1:{i:0;s:16:"system.menu.main";}s:6:"module";a:1:{i:0;s:10:"menu_block";}s:5:"theme";a:1:{i:0;s:6:"bartik";}}s:2:"id";s:14:"mainnavigation";s:5:"theme";s:6:"bartik";s:6:"region";s:12:"primary_menu";s:6:"weight";i:0;s:8:"provider";N;s:6:"plugin";s:15:"menu_block:main";s:8:"settings";a:13:{s:2:"id";s:15:"menu_block:main";s:5:"label";s:15:"Main navigation";s:8:"provider";s:10:"menu_block";s:13:"label_display";s:7:"visible";s:6:"follow";b:0;s:13:"follow_parent";s:5:"child";s:5:"level";i:1;s:5:"depth";i:0;s:6:"expand";b:0;s:6:"parent";s:5:"main:";s:10:"suggestion";s:4:"main";s:22:"only_translated_labels";i:1;s:23:"only_translated_content";i:1;}s:10:"visibility";a:0:{}}',
    'collection' => '',
  ])
  ->execute();
$connection->insert('config')
  ->fields(['name', 'data'])
  ->values([
    'name' => 'block.block.mainnavigation_2',
    'data' => 'a:12:{s:4:"uuid";s:36:"0c1b51cb-b578-4059-b2da-d5953c5f7531";s:8:"langcode";s:2:"en";s:6:"status";b:1;s:12:"dependencies";a:3:{s:6:"config";a:1:{i:0;s:16:"system.menu.main";}s:6:"module";a:1:{i:0;s:6:"system";}s:5:"theme";a:1:{i:0;s:6:"bartik";}}s:2:"id";s:16:"mainnavigation_2";s:5:"theme";s:6:"bartik";s:6:"region";s:12:"primary_menu";s:6:"weight";i:0;s:8:"provider";N;s:6:"plugin";s:22:"system_menu_block:main";s:8:"settings";a:9:{s:2:"id";s:22:"system_menu_block:main";s:5:"label";s:15:"Main navigation";s:8:"provider";s:6:"system";s:13:"label_display";s:7:"visible";s:5:"level";i:1;s:5:"depth";i:0;s:16:"expand_all_items";b:0;s:22:"only_translated_labels";i:1;s:23:"only_translated_content";i:1;}s:10:"visibility";a:0:{}}',
    'collection' => '',
  ])
  ->execute();
$connection->insert('config')
  ->fields(['name', 'data'])
  ->values([
    'name' => 'block.block.pagetitle',
    'data' => 'a:12:{s:4:"uuid";s:36:"9df27e0e-ce38-4dca-9ecc-937232d41ea3";s:8:"langcode";s:2:"en";s:6:"status";b:1;s:12:"dependencies";a:1:{s:5:"theme";a:1:{i:0;s:6:"bartik";}}s:2:"id";s:9:"pagetitle";s:5:"theme";s:6:"bartik";s:6:"region";s:12:"primary_menu";s:6:"weight";i:0;s:8:"provider";N;s:6:"plugin";s:16:"page_title_block";s:8:"settings";a:4:{s:2:"id";s:16:"page_title_block";s:5:"label";s:10:"Page title";s:8:"provider";s:4:"core";s:13:"label_display";s:1:"0";}s:10:"visibility";a:0:{}}',
    'collection' => '',
  ])
  ->execute();
