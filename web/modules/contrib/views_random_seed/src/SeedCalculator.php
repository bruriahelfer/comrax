<?php

namespace Drupal\views_random_seed;

use Drupal\Component\Datetime\Time;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Calculates seeds.
 */
class SeedCalculator {

  /**
  * The server time wrapper.
  *
  * @var \Drupal\Component\Datetime\Time
  */
  protected $serverTime;

  /**
   * The key value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValueStore;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Array of views options.
   *
   * @var array
   */
  protected $options;

  /**
   * SeedCalculator constructor.
   *
   * @param \Drupal\Component\Datetime\Time $time
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValueFactory
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(Time $time, KeyValueFactoryInterface $keyValueFactory, AccountInterface $current_user) {
    $this->serverTime = $time;
    $this->keyValueStore = $keyValueFactory->get('views_random_seed');
    $this->currentUser = $current_user;
  }

  /**
   * Calculate a seed.
   *
   * @param array $options
   *   The options for the random seed handler.
   * @param string $view_name
   *   The name of the view.
   * @param string $display
   *   The current display.
   * @param string $db_type
   *   The current database type (mysql(i) - pgsql).
   *
   * @return int
   *   Seed value which is a timestamp.
   */
  public function calculateSeed(array $options, string $view_name, string $display, string $db_type) {
    $time = $this->serverTime->getRequestTime();
    $seed_name = 'views_seed_name-' . $view_name . '-' . $display;

    // Reuse from other view.
    if (!empty($options['reuse_seed'])) {
      $seed_name = 'views_seed_name-' . $options['reuse_seed'];
    }

    $this->options = $options;
    $seed = $this->getSeed($seed_name);
    $this->debug('Seed in storage: ' . $seed);
    $this->debug('Current time: ' . $time);

    $options += ['user_seed_type' => 'same_per_user'];

    // Create a first seed if necessary.
    if ($seed === FALSE) {
      $this->debug('No feed in storage, generating.');
      $seed = $this->generateSeed($seed_name, $time, $db_type);
    }

    // Reset seed or not? -1 is never, 0 is custom.
    if ($options['reset_seed_int'] != -1) {
      $reset_time = $options['reset_seed_int'] === 0 ? $options['reset_seed_custom'] : $options['reset_seed_int'];
      $this->debug('reset time: ' . $reset_time);
      $this->debug('seed time: ' . $seed);
      $this->debug('compare: ' . ($seed + $reset_time));
      if (($seed + $reset_time) < $time) {
        $this->debug('Resetting seed.');
        $seed = $this->generateSeed($seed_name, $time, $db_type);
        // Invalidate cache for the current view when generating a new seed.
        Cache::invalidateTags(["views_random_seed-{$view_name}-{$display}"]);
      }
    }

    // Return seed.
    return $seed;
  }

  /**
   * Helper function to generate a seed
   *
   * @param string $seed_name
   *   Name of the seed.
   * @param int $time
   *   Current timestamp.
   * @param string $db_type
   *   The current database type (mysql(i) - pgsql).
   *
   * @return int
   *   The seed value.
   */
  protected function generateSeed(string $seed_name, int $time, string $db_type) {
    $seed = $this->createInt($time, $db_type);
    $user_seed_type = $this->options['user_seed_type'];

    if ($user_seed_type === 'diff_per_user' && ($this->currentUser->isAuthenticated() || $this->options['anonymous_session'])) {
      $this->debug('Generate diff per user');
      $_SESSION[$seed_name] = $seed;
    }
    else {
      $this->debug('Generate same per user');
      $this->keyValueStore->set($seed_name, $seed);
    }

    return $seed;
  }

  /**
   * Helper function to create a seed based on db_type. MySQL can handle any
   * integer in the RAND() function, Postgres needs an int between 0 and 1.
   *
   * @param int $time
   *   The current timestamp.
   * @param string $db_type
   *   The current database type (mysql(i) - pgsql)
   *
   * @return int $seed timestamp or int between 0 and 1.
   */
  protected function createInt(int $time, string $db_type) {
    switch ($db_type) {
      case 'mysql':
      case 'mysqli':
      default:
        return $time;
      case 'pgsql':
        return $time / 10000000000;
    }
  }

  /**
   * Get the seed either from session or store.
   *
   * @param $seed_name
   *
   * @return mixed
   */
  protected function getSeed($seed_name) {
    $user_seed_type = $this->options['user_seed_type'];
    if ($user_seed_type === 'diff_per_user' && ($this->currentUser->isAuthenticated() || $this->options['anonymous_session'])) {
      $this->debug('getSeed: diff per user');
      return $this->getSeedFromSession($seed_name);
    }
    else {
      $this->debug('getSeed: same per user');
      return $this->keyValueStore->get($seed_name, FALSE);
    }
  }

  /**
   * Get the seed from session.
   *
   * @return int|false
   */
  protected function getSeedFromSession($seed_name) {
    return $_SESSION[$seed_name] ?? FALSE;
  }

  /**
   * Quick debugger via Drupal messages.
   *
   * @param $string
   */
  protected function debug($string) {
    if (Settings::get('views_random_seed_view_messages', FALSE)) {
      \Drupal::messenger()->addMessage($string);
    }
  }

}

