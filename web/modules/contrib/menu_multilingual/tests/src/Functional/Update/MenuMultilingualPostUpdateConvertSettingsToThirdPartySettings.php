<?php

namespace Drupal\Tests\menu_multilingual\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Class MenuMultilingualPostUpdateConvertSettingsToThirdPartySettings.
 *
 * Update test that checks if the settings are correctly converted.
 *
 * @group menu_multilingual
 */
class MenuMultilingualPostUpdateConvertSettingsToThirdPartySettings extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'menu_multilingual',
    'menu_block',
    'block',
  ];

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $blockStorage;

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-8.8.0.bare.standard.php.gz',
      __DIR__ . '/../../../../../menu_multilingual/tests/fixtures/update/menu-multilingual-block-settings.php',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->blockStorage = $this->container->get('entity_type.manager')->getStorage('block');
  }

  /**
   * Post update test that checks that the settings are correctly converted.
   *
   * @see menu_multilingual_post_update_convert_settings_to_third_party_settings
   */
  public function testPostUpdateConvertSettingsToThirdPartySettings() {
    /** @var \Drupal\block\BlockInterface $menu_block */
    $menu_block = $this->blockStorage->load('mainnavigation');
    /** @var \Drupal\block\BlockInterface $system_menu_block */
    $system_menu_block = $this->blockStorage->load('mainnavigation_2');
    /** @var \Drupal\block\BlockInterface $non_menu_block */
    $non_menu_block = $this->blockStorage->load('pagetitle');

    $menu_block_settings = $menu_block->get('settings');
    $this->assertTrue((bool) $menu_block_settings['only_translated_labels']);
    $this->assertTrue((bool) $menu_block_settings['only_translated_content']);
    $this->assertNull($menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
    $this->assertNull($menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));

    $system_menu_block_settings = $system_menu_block->get('settings');
    $this->assertTrue((bool) $system_menu_block_settings['only_translated_labels']);
    $this->assertTrue((bool) $system_menu_block_settings['only_translated_content']);
    $this->assertNull($system_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
    $this->assertNull($system_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));

    $non_menu_block_settings = $non_menu_block->get('settings');
    $this->assertArrayNotHasKey('only_translated_labels', $non_menu_block_settings);
    $this->assertArrayNotHasKey('only_translated_content', $non_menu_block_settings);
    $this->assertNull($system_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
    $this->assertNull($system_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));

    $this->runUpdates();

    $menu_block = $this->blockStorage->load('mainnavigation');
    $system_menu_block = $this->blockStorage->load('mainnavigation_2');
    $non_menu_block = $this->blockStorage->load('pagetitle');

    $menu_block_settings = $menu_block->get('settings');
    $this->assertArrayNotHasKey('only_translated_labels', $menu_block_settings);
    $this->assertArrayNotHasKey('only_translated_content', $menu_block_settings);
    $this->assertTrue($menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
    $this->assertTrue($menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));

    $system_menu_block_settings = $system_menu_block->get('settings');
    $this->assertArrayNotHasKey('only_translated_labels', $system_menu_block_settings);
    $this->assertArrayNotHasKey('only_translated_content', $system_menu_block_settings);
    $this->assertTrue($system_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
    $this->assertTrue($system_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));

    $non_menu_block_settings = $non_menu_block->get('settings');
    $this->assertArrayNotHasKey('only_translated_labels', $non_menu_block_settings);
    $this->assertArrayNotHasKey('only_translated_content', $non_menu_block_settings);
    $this->assertNull($non_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_labels'));
    $this->assertNull($non_menu_block->getThirdPartySetting('menu_multilingual', 'only_translated_content'));
  }

}
