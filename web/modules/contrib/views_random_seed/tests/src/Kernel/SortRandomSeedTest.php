<?php

namespace Drupal\Tests\views_random_seed\Kernel;

use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Tests random seed handler.
 *
 * Inspired by SortRandomTest in views.
 *
 * @group views_random_seed
 */
class SortRandomSeedTest extends ViewsKernelTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_view_random_seed'];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'path_alias',
    'system',
    'views',
    'views_test_config',
    'views_test_data',
    'views_random_seed',
    'views_random_seed_config',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp(FALSE);
    ViewTestData::createTestViews(get_class($this), ['views_random_seed_config']);
  }

  /**
   * Add more items to the test set, to make the order tests more robust.
   */
  protected function dataSet() {
    $data = parent::dataSet();
    for ($i = 0; $i < 55; $i++) {
      $data[] = [
        'name' => 'name_' . $i,
        'age' => $i,
        'job' => 'job_' . $i,
        'created' => rand(0, time()),
        'status' => 1,
      ];
    }
    return $data;
  }

  /**
   * Return a basic view with random ordering.
   */
  protected function getBasicRandomView() {
    $view = Views::getView('test_view_random_seed');
    $view->setDisplay();

    return $view;
  }

  /**
   * Reset an executed view.
   *
   * @param \Drupal\views\ViewExecutable $view
   */
  protected function resetExecution(ViewExecutable $view) {
    $view->built = FALSE;
    $view->executed = FALSE;
    $view->result = [];
  }

  /**
   * Executes a view.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view object.
   * @param array $args
   *   (optional) An array of the view arguments to use for the view.
   * @param bool $reset
   *   (optional) Reset the already executed view.
   */
  protected function executeView($view, array $args = [], $reset = FALSE) {

    if ($reset) {
      $this->resetExecution($view);
    }

    $view->setDisplay();
    $view->preExecute($args);
    $view->execute();
  }

  /**
   * Tests random ordering of the result set.
   */
  public function testRandomFixedOrdering() {

    $view_random = $this->getBasicRandomView();
    $this->executeView($view_random);
    $result = $view_random->result;

    // Execute again and verify it's the same.
    $this->executeView($view_random, [], TRUE);
    $this->assertIdenticalResultset($view_random, $result, [
      'views_test_data_name' => 'views_test_data_name',
      'views_test_data_age' => 'views_test_data_age',
    ]);

  }

}
