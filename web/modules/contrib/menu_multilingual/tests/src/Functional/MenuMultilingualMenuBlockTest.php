<?php

namespace Drupal\Tests\menu_multilingual\Functional;

/**
 * Class MenuMultilingualMenuBlockTest.
 *
 * Tests for Menu Multilingual module integration with menu_block.
 *
 * @group MenuMultilingualMenuBlockTest
 */
class MenuMultilingualMenuBlockTest extends MenuMultilingualTest {

  /**
   * {@inheritdoc}
   */
  public $menuBlockConfigPath = 'admin/structure/block/manage/mainnavigation';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'menu_block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Remove the default main menu and replace it with a menu_block.
    $this->drupalGet('admin/structure/block/manage/stark_main_menu/delete');
    $this->submitForm([], 'Remove');
    $this->drupalGet('admin/structure/block/add/menu_block:main/stark');
    $this->submitForm([
      'id'                                => 'mainnavigation',
      'settings[label]'                   => 'Main navigation',
      'settings[label_display]'           => FALSE,
      'settings[level]'                   => 1,
      'settings[depth]'                   => 0,
      'settings[expand_all_items]'        => 1,
      'settings[suggestion]'              => 'main',
      'third_party_settings[menu_multilingual][only_translated_labels]'  => FALSE,
      'third_party_settings[menu_multilingual][only_translated_content]' => FALSE,
      'region'                            => 'primary_menu',
    ], 'Save block');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Display Menu Multilingual form.
   */
  public function testMenuMultilingualFormDisplay() {
    parent::testMenuMultilingualFormDisplay();
    // Check if menu_block form fields are displayed.
    $this->assertSession()->pageTextContains("Advanced options");
    $this->assertSession()->pageTextContains("HTML and style options");
  }

}
