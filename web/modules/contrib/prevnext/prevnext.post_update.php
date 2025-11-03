<?php

/**
 * @file
 * Contains prevnext.post_update.php.
 */

/**
 * Update prevnext-2.0.x config to 3.0.x schema.
 */
function prevnext_post_update_configs() {
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('prevnext.settings');

  if ($config->get('prevnext_enabled_nodetypes') === NULL) {
    return t('No prevnext configuration update needed.');
  }

  // Get values from "prevnext_enabled_nodetypes".
  $enabled_nodetypes = $config->get('prevnext_enabled_nodetypes');
  $config->set('prevnext_enabled_entity_types', ['node' => 'node']);
  $config->set('prevnext_enabled_entity_bundles', [
    'node' => $enabled_nodetypes,
  ]);

  // Remove obsolete "prevnext_enabled_nodetypes".
  $config->clear('prevnext_enabled_nodetypes');
  $config->clear('prevnext_premission_check');
  $config->save();

  return t('Prevnext configuration schema updated to 3.0.x');
}
