<?php

namespace Drupal\Tests\prevnext\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\node\Functional\NodeTestBase;

/**
 * Class PrevNextTest. The base class for prevnext links.
 */
class PrevNextTest extends NodeTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'prevnext'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test prevnext links.
   */
  public function testPrevNextLinks() {
    // Log in as an admin user with permission to manage prevnext settings.
    $admin = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($admin);

    // Create a new node type.
    $this->createContentType(['type' => 'prevnext']);

    // Enable prevnext links for node entity type.
    $config = \Drupal::configFactory()->getEditable('prevnext.settings');
    $config->set('prevnext_enabled_entity_types', ['node' => 'node']);
    $config->set('prevnext_enabled_entity_bundles', ['node' => ['prevnext' => 'prevnext']]);
    $config->save();

    // Clear the cache.
    Cache::invalidateTags(['entity_field_info']);

    /** @var \Drupal\Core\Entity\EntityDisplayRepository $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');

    // Add prevnext links to the output.
    $display_repository->getViewDisplay('node', 'prevnext', 'default')
      ->setComponent('prevnext_next')
      ->setComponent('prevnext_previous')
      ->save();

    // Check the status of the page.
    $this->drupalGet('node/add/prevnext');
    $this->assertSession()->statusCodeEquals(200);

    // Create a few nodes with a random names.
    $titles = [
      $this->randomMachineName(8),
      $this->randomMachineName(8),
      $this->randomMachineName(8),
    ];

    foreach ($titles as $title) {
      $edit = [];
      $edit['title[0][value]'] = $title;
      $this->drupalGet('node/add/prevnext');
      $this->submitForm($edit, 'Save');
    }

    // Get all created nodes.
    $nodes = [
      $this->drupalGetNodeByTitle($titles[0]),
      $this->drupalGetNodeByTitle($titles[1]),
      $this->drupalGetNodeByTitle($titles[2]),
    ];

    // Clear the cache.
    Cache::invalidateTags([
      "node:{$nodes[0]->id()}",
      "node:{$nodes[1]->id()}",
      "node:{$nodes[2]->id()}",
    ]);

    // Check if the output contains required strings.
    $this->drupalGet("node/{$nodes[0]->id()}");
    $this->assertSession()->pageTextContains($titles[0]);
    $this->assertSession()->pageTextContains(t('Next'));

    $this->drupalGet("node/{$nodes[1]->id()}");
    $this->assertSession()->pageTextContains($titles[1]);
    $this->assertSession()->pageTextContains(t('Previous'));
    $this->assertSession()->pageTextContains(t('Next'));

    $this->drupalGet("node/{$nodes[2]->id()}");
    $this->assertSession()->pageTextContains($titles[2]);
    $this->assertSession()->pageTextContains(t('Previous'));
  }

}
