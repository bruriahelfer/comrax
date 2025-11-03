<?php

namespace Drupal\Tests\menu_multilingual\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Class MenuMultilingualTest.
 *
 * Tests for Menu Multilingual module.
 *
 * @group MenuMultilingualTest
 */
class MenuMultilingualTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var string
   */
  public $menuBlockConfigPath = 'admin/structure/block/manage/stark_main_menu';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'content_translation',
    'config_translation',
    'language',
    'menu_link_content',
    'menu_multilingual',
    'menu_ui',
    'node',
    'views_ui',
  ];

  /**
   * The menu link content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuLinkContentStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->rootUser);

    // Place the main menu to the page.
    $this->drupalPlaceBlock('system_menu_block:main', ['id' => 'stark_main_menu']);

    // Create an article content type.
    $this->drupalCreateContentType(['type' => 'article']);

    // Add FR language.
    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Set prefixes to en and fr.
    $this->drupalGet('admin/config/regional/language/detection/url');
    $this->submitForm([
      'prefix[en]' => 'en',
      'prefix[fr]' => 'fr',
    ], 'Save configuration');

    // Set up URL language negotiation.
    $this->drupalGet('admin/config/regional/language/detection');
    $this->submitForm([
      'language_interface[enabled][language-url]' => 1,
    ], 'Save settings');

    $this->drupalGet('admin/config/regional/content-language');
    $this->submitForm([
      "entity_types[node]"                                                                     => 1,
      "settings[node][article][translatable]"                                                  => 1,
      "settings[node][article][settings][language][language_alterable]"                        => 1,
      "entity_types[menu_link_content]"                                                        => 1,
      "settings[menu_link_content][menu_link_content][translatable]"                           => 1,
      "settings[menu_link_content][menu_link_content][settings][language][language_alterable]" => 1,
    ], 'Save configuration');
    $this->container->get('entity_type.manager')->clearCachedDefinitions();

    // Create a translated node.
    $edit = [
      'title[0][value]'    => 'Node 1 translated English',
      'langcode[0][value]' => 'en',
    ];
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');
    $edit = [
      'title[0][value]'    => 'Node 1 translated French',
    ];
    $this->drupalGet('node/1/translations/add/en/fr');
    $this->submitForm($edit, 'Save');
    // Create an untranslated node.
    $edit = [
      'title[0][value]'    => 'Node 2 untranslated English',
      'langcode[0][value]' => 'en',
    ];
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');
    // Create two language neutral nodes.
    $edit = [
      'title[0][value]'    => 'Node 3 Language Not Applicable',
      'langcode[0][value]' => 'zxx',
    ];
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');
    $edit = [
      'title[0][value]'    => 'Node 4 Language Not Specified',
      'langcode[0][value]' => 'und',
    ];
    $this->drupalGet('node/add/article');
    $this->submitForm($edit, 'Save');

    $this->menuLinkContentStorage = $this->container->get('entity_type.manager')->getStorage('menu_link_content');
  }

  /**
   * Display Menu Multilingual form.
   */
  public function testMenuMultilingualFormDisplay() {
    $this->drupalGet($this->menuBlockConfigPath);
    $this->assertSession()->pageTextContains("Hide menu items without translated label");
    $this->assertSession()->pageTextContains("Hide menu items without translated content");
  }

  /**
   * Test for translated Custom Menu Items.
   */
  public function testCustomMenuItemTranslated() {
    // Create 4 translated custom menu links in the main menu.
    $this->createCustomMenuItemTranslated('Node 1 translated', 1);
    $this->createCustomMenuItemTranslated('Node 2 untranslated', 2);
    $this->createCustomMenuItemTranslated('Node 3 Language Not Applicable', 3);
    $this->createCustomMenuItemTranslated('Node 4 Language Not Specified', 4);
    $session = $this->assertSession();

    // Test defaults without any configurations.
    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, French menu item');
    $session->pageTextContains('Node 2 untranslated, French menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, French menu item');
    $session->pageTextContains('Node 4 Language Not Specified, French menu item');

    // Test 'Hide menu items without translated label'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 0,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, French menu item');
    $session->pageTextContains('Node 2 untranslated, French menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, French menu item');
    $session->pageTextContains('Node 4 Language Not Specified, French menu item');

    // Test 'Hide menu items without translated label' and
    // 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, French menu item');
    $session->pageTextNotContains('Node 2 untranslated, French menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, French menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, French menu item');

    // Test 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 0,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, French menu item');
    $session->pageTextNotContains('Node 2 untranslated, French menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, French menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, French menu item');
  }

  /**
   * Test for untranslated Custom Menu Items.
   */
  public function testCustomMenuItemUntranslated() {
    // Create 4 translated custom menu links in the main menu.
    $this->createCustomMenuItemUntranslated('Node 1 translated', 1);
    $this->createCustomMenuItemUntranslated('Node 2 untranslated', 2);
    $this->createCustomMenuItemUntranslated('Node 3 Language Not Applicable', 3);
    $this->createCustomMenuItemUntranslated('Node 4 Language Not Specified', 4);

    $session = $this->assertSession();

    // Test defaults without any configurations.
    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextContains('Node 4 Language Not Specified, English menu item');

    // Test 'Hide menu items without translated label'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 0,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextNotContains('Node 1 translated, English menu item');
    $session->pageTextNotContains('Node 2 untranslated, English menu item');
    $session->pageTextNotContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');

    // Test 'Hide menu items without translated label' and
    // 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextNotContains('Node 1 translated, English menu item');
    $session->pageTextNotContains('Node 2 untranslated, English menu item');
    $session->pageTextNotContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');

    // Test 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 0,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, English menu item');
    $session->pageTextNotContains('Node 2 untranslated, English menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, English menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, English menu item');
  }

  /**
   * Test for Custom Menu Items with Language Not Applicable.
   */
  public function testCustomMenuItemLanguageNotApplicable() {
    // Create 4 translated custom menu links in the main menu.
    $this->createCustomMenuItemLanguageNotApplicable('Node 1 translated', 1);
    $this->createCustomMenuItemLanguageNotApplicable('Node 2 untranslated', 2);
    $this->createCustomMenuItemLanguageNotApplicable('Node 3 Language Not Applicable', 3);
    $this->createCustomMenuItemLanguageNotApplicable('Node 4 Language Not Specified', 4);

    $session = $this->assertSession();

    // Test defaults without any configurations.
    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextContains('Node 4 Language Not Specified, Language Not Applicable menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextContains('Node 4 Language Not Specified, Language Not Applicable menu item');

    // Test 'Hide menu items without translated label'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 0,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextContains('Node 4 Language Not Specified, Language Not Applicable menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextContains('Node 4 Language Not Specified, Language Not Applicable menu item');

    // Test 'Hide menu items without translated label' and
    // 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Applicable menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Applicable menu item');

    // Test 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 0,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Applicable menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, Language Not Applicable menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Applicable menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Applicable menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Applicable menu item');
  }

  /**
   * Test for Custom Menu Items with Language Not Specified.
   */
  public function testCustomMenuItemLanguageNotSpecified() {
    // Create 4 translated custom menu links in the main menu.
    $this->createCustomMenuItemLanguageNotSpecified('Node 1 translated', 1);
    $this->createCustomMenuItemLanguageNotSpecified('Node 2 untranslated', 2);
    $this->createCustomMenuItemLanguageNotSpecified('Node 3 Language Not Applicable', 3);
    $this->createCustomMenuItemLanguageNotSpecified('Node 4 Language Not Specified', 4);

    $session = $this->assertSession();

    // Test defaults without any configurations.
    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextContains('Node 4 Language Not Specified, Language Not Specified menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextContains('Node 4 Language Not Specified, Language Not Specified menu item');

    // Test 'Hide menu items without translated label'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 0,
    ]);

    $this->drupalGet('node');
    $session->pageTextNotContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Specified menu item');
    $this->drupalGet('fr/node');
    $session->pageTextNotContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Specified menu item');

    // Test 'Hide menu items without translated label' and
    // 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextNotContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Specified menu item');
    $this->drupalGet('fr/node');
    $session->pageTextNotContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Specified menu item');

    // Test 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 0,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Specified menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Node 1 translated, Language Not Specified menu item');
    $session->pageTextNotContains('Node 2 untranslated, Language Not Specified menu item');
    $session->pageTextContains('Node 3 Language Not Applicable, Language Not Specified menu item');
    $session->pageTextNotContains('Node 4 Language Not Specified, Language Not Specified menu item');
  }

  /**
   * Test for View translated Menu Items.
   */
  public function testViewsMenuItemTranslated() {
    // Create 4 translated custom menu links in the main menu.
    $this->createViewsMenuItemTranslated();

    $session = $this->assertSession();

    // Test defaults without any configurations.
    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Views French menu item');

    // Test 'Hide menu items without translated label'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 0,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Views French menu item');

    // Test 'Hide menu items without translated label' and
    // 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Views French menu item');

    // Test 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 0,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Views French menu item');

  }

  /**
   * Test for View untranslated Menu Items.
   */
  public function testViewsMenuItemUntranslated() {
    // Create 4 translated custom menu links in the main menu.
    $this->createViewsMenuItemUntranslated();

    $session = $this->assertSession();

    // Test defaults without any configurations.
    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Views English menu item');

    // Test 'Hide menu items without translated label'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 0,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextNotContains('Views English menu item');

    // Test 'Hide menu items without translated label' and
    // 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 1,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextNotContains('Views English menu item');

    // Test 'Hide menu items without translated content'.
    $this->submitBlockForm([
      'third_party_settings[menu_multilingual][only_translated_labels]'  => 0,
      'third_party_settings[menu_multilingual][only_translated_content]' => 1,
    ]);

    $this->drupalGet('node');
    $session->pageTextContains('Views English menu item');
    $this->drupalGet('fr/node');
    $session->pageTextContains('Views English menu item');
  }

  /**
   * Returns translated Custom Menu Items.
   */
  public function createCustomMenuItemTranslated(string $title, int $nid) {
    $edit = [
      'title[0][value]'    => $title . ', English menu item',
      'link[0][uri]' => '/node/' . $nid,
      'langcode[0][value]' => 'en',
    ];
    $this->drupalGet('admin/structure/menu/manage/main/add');
    $this->submitForm($edit, 'Save');

    $menu_links = $this->menuLinkContentStorage->loadByProperties(['title' => $title . ', English menu item']);
    $menu_link = current($menu_links);

    $edit = [
      'title[0][value]'    => $title . ', French menu item',
      'link[0][uri]' => '/node/' . $nid,
    ];
    $this->drupalGet('admin/structure/menu/item/' . $menu_link->id() . '/edit/translations/add/en/fr');
    $this->submitForm($edit, 'Save');
  }

  /**
   * Returns untranslated Custom Menu Items.
   */
  public function createCustomMenuItemUntranslated(string $title, int $nid) {
    $edit = [
      'title[0][value]'    => $title . ', English menu item',
      'link[0][uri]' => '/node/' . $nid,
      'langcode[0][value]' => 'en',
    ];
    $this->drupalGet('admin/structure/menu/manage/main/add');
    $this->submitForm($edit, 'Save');
  }

  /**
   * Returns Custom Menu Items with language Not Applicable.
   */
  public function createCustomMenuItemLanguageNotApplicable(string $title, int $nid) {
    $edit = [
      'title[0][value]'    => $title . ', Language Not Applicable menu item',
      'link[0][uri]' => '/node/' . $nid,
      'langcode[0][value]' => 'zxx',
    ];
    $this->drupalGet('admin/structure/menu/manage/main/add');
    $this->submitForm($edit, 'Save');
  }

  /**
   * Returns Custom Menu Items with language Not Specified.
   */
  public function createCustomMenuItemLanguageNotSpecified(string $title, int $nid) {
    $edit = [
      'title[0][value]'    => $title . ', Language Not Specified menu item',
      'link[0][uri]' => '/node/' . $nid,
      'langcode[0][value]' => 'und',
    ];
    $this->drupalGet('admin/structure/menu/manage/main/add');
    $this->submitForm($edit, 'Save');
  }

  /**
   * Returns Views Menu Items translated.
   */
  public function createViewsMenuItemTranslated() {
    $edit = [
      'menu[title]' => 'Views English menu item',
      'menu[type]'    => 'normal',
      'menu[parent]' => 'main:',
    ];
    $this->drupalGet('admin/structure/views/nojs/display/content/page_1/menu');
    $this->submitForm($edit, 'Apply');
    $this->drupalGet('admin/structure/views/view/content/edit/page_1');
    $this->submitForm([], 'Save');
    $edit = [
      'translation[config_names][views.view.content][display][page_1][display_options][menu][title]' => 'Views French menu item',
    ];
    $this->drupalGet('admin/structure/views/view/content/translate/fr/add');
    $this->submitForm($edit, 'Save translation');
  }

  /**
   * Returns Views Menu Items untranslated.
   */
  public function createViewsMenuItemUntranslated() {
    $edit = [
      'menu[title]' => 'Views English menu item',
      'menu[type]'    => 'normal',
      'menu[parent]' => 'main:',
    ];
    $this->drupalGet('admin/structure/views/nojs/display/content/page_1/menu');
    $this->submitForm($edit, 'Apply');
    $this->drupalGet('admin/structure/views/view/content/edit/page_1');
    $this->submitForm([], 'Save');
  }

  /**
   * Submits the given form data to the block form.
   *
   * A separate function was provided for submitting the values to the block
   * form, so this test class can be reused for the tests with the context
   * module.
   *
   * @param array $edit
   *   The form data.
   */
  protected function submitBlockForm(array $edit): void {
    $this->drupalGet($this->menuBlockConfigPath);
    $this->submitForm($edit, 'Save block');
  }

}
