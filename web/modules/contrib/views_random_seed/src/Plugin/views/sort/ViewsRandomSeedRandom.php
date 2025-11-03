<?php

namespace Drupal\views_random_seed\Plugin\views\sort;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\sort\SortPluginBase;
use Drupal\views\Views;
use Drupal\views_random_seed\SeedCalculator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;

/**
 * Handle a random sort with seed.
 *
 * @ViewsSort("views_random_seed_random")
 */
class ViewsRandomSeedRandom extends SortPluginBase {

  /** @var \Drupal\views\Plugin\views\query\Sql */
  public $query;

  /**
   * The seed calculator.
   *
   * @var \Drupal\views_random_seed\SeedCalculator
   */
  protected $seedCalculator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SeedCalculator $seedCalculator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->seedCalculator = $seedCalculator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('views_random_seed.seed_calculator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['reuse_seed'] = ['default' => ''];
    $options['user_seed_type'] = ['default' => 'same_per_user'];
    $options['anonymous_session'] = ['default' => FALSE];
    $options['reset_seed_int'] = ['default' => '3600'];
    $options['reset_seed_custom'] = ['default' => '300'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['order']['#access'] = FALSE;
    $reuseSeedArray = ['' => $this->t('None')];
    $viewList = Views::getAllViews();
    foreach ($viewList as $view){
      foreach ($view->get('display') as $display) {
        $reuseSeedArray[$view->id() . '-' . $display['id']] = $view->label() . ': ' . $display['display_title'];
      }
    }

    // Reuse seed from another view.
    $form['reuse_seed'] = [
      '#type' => 'select',
      '#title' => $this->t('Reuse seed from another view display'),
      '#options' => $reuseSeedArray,
      '#description' => $this->t('With this option enabled, you can sync results between views.'),
      '#default_value' => $this->options['reuse_seed'] ?? '',
    ];

    // User seed type.
    $form['user_seed_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('User seed type'),
      '#options' => [
        'same_per_user' => $this->t('Use the same seed for every user'),
        'diff_per_user' => $this->t('Use a different seed per user'),
      ],
      '#default_value' => $this->options['user_seed_type'] ?? 'same_per_user',
    ];

    // User seed anonymous.
    $form['anonymous_session'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Anonymous sessions'),
      '#default_value' => $this->options['anonymous_session'] ?? FALSE,
      '#description' => $this->t('Start sessions for anonymous users, which impacts performance.<br />If left unchecked, the seed will be the same for all anonymous users.'),
      '#states' => [
        'visible' => [':input[name="options[user_seed_type]"]' => ['value' => 'diff_per_user']]
      ],
    ];

    // User seed type.
    $form['reset_seed_int'] = [
      '#type' => 'radios',
      '#title' => $this->t('Reset seed'),
      '#options' => [
        -1 => $this->t('Never'),
        0 => $this->t('Custom'),
        3600 => $this->t('Every hour'),
        28800 => $this->t('Every eight hours'),
        86400 => $this->t('Every day'),
      ],
      '#default_value' => $this->options['reset_seed_int'] ?? '3600',
    ];

    // Custom time.
    $form['reset_seed_custom'] = [
      '#type' => 'number',
      '#min' => 0,
      '#size' => 10,
      '#title' => $this->t('Custom reset seed'),
      '#required' => TRUE,
      '#default_value' => $this->options['reset_seed_custom'] ?? '300',
      '#description' => $this->t('Define your own custom reset time in seconds.'),
      '#states' => [
        'visible' => [':input[name="options[reset_seed_int]"]' => ['value' => '0']]
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $db_type = \Drupal::database()->driver();
    $seed = $this->seedCalculator->calculateSeed($this->options, $this->view->id(), $this->view->current_display, $db_type);
    switch ($db_type) {
      case 'mysql':
      case 'mysqli':
        $formula = 'RAND(' . $seed . ')';
        break;
      case 'pgsql':
        // For PgSQL we'll run an extra query with a integer between
        // 0 and 1 which will be used by the RANDOM() function.
        \Drupal::database()->query('select setseed(' . $seed . ')');
        \Drupal::database()->query("select random()");
        $formula = 'RANDOM()';
        break;
    }

    if (!empty($formula)) {
      // Use SearchAPI random sorting with seed if the query object is an
      // instance of SearchApiViewsQuery (or a subclass of it). Pass along the
      // seed and the built formula as options for the SearchApiQuery class.
      // See: https://www.drupal.org/node/1197538#comment-10190520
      if ($this->view->query instanceof SearchApiQuery) {
        $this->query->addOrderBy('rand', NULL, $this->options['order'], '', [
          'seed' => $seed,
          'formula' => $formula,
        ]);
      }
      else {
        $this->query->addOrderBy(NULL, $formula, $this->options['order'], '_' . $this->field);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    if ($this->options['user_seed_type'] === 'diff_per_user') {
      $contexts[] = 'user';
    }
    return $contexts;
  }

}
