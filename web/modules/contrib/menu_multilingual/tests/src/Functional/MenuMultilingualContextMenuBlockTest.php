<?php

namespace Drupal\Tests\menu_multilingual\Functional;

/**
 * Tests for Menu Multilingual module integration with context and menu block.
 *
 * @group menu_multilingual
 */
class MenuMultilingualContextMenuBlockTest extends MenuMultilingualMenuBlockTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'context_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Remove the default main menu and replace it with a menu_block added by
    // context.
    $this->drupalGet('admin/structure/block/manage/mainnavigation/delete');
    $this->submitForm([], 'Remove');

    $this->drupalGet('admin/structure/context/add');
    $this->submitForm([
      'label' => 'General',
      'name' => 'general',
    ], 'Save');
    $this->clickLink('Add reaction');
    $this->clickLink('Blocks');
    $this->clickLink('Place block');
    $this->drupalGet('admin/structure/context/general/reaction/blocks/blocks/add/menu_block:main');
    $this->submitForm([], 'Add block');
    $this->submitForm([], 'Save and continue');

    $this->clickLink('Edit');
    $this->menuBlockConfigPath = $this->getSession()->getCurrentUrl();
  }

  /**
   * {@inheritdoc}
   */
  protected function submitBlockForm(array $edit): void {
    $this->drupalGet($this->menuBlockConfigPath);
    $this->submitForm($edit, 'Update block');
    $this->submitForm([], 'Save and continue');
  }

}
