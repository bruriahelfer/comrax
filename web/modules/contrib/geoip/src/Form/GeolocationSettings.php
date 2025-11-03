<?php

namespace Drupal\geoip\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geoip\GeoLocatorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form to configure GeoIP.
 */
class GeolocationSettings extends ConfigFormBase {

  /**
   * The plugin manager.
   *
   * @var \Drupal\geoip\GeoLocatorManager
   */
  protected $geoLocatorManager;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\geoip\GeoLocatorManager $geo_locator_manager
   *   The geo locator manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, GeoLocatorManager $geo_locator_manager) {
    parent::__construct($config_factory);
    $this->geoLocatorManager = $geo_locator_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.geolocator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['geoip.geolocation'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geoip_geolocation_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('geoip.geolocation');

    $form['plugin_id'] = [
      '#type' => 'tableselect',
      '#multiple' => FALSE,
      '#header' => [
        'label' => $this->t('Label'),
        'description' => $this->t('Description'),
      ],
      '#options' => [],
      '#default_value' => $config->get('plugin_id'),
    ];

    foreach ($this->geoLocatorManager->getDefinitions() as $plugin_id => $definition) {
      $form['plugin_id']['#options'][$plugin_id] = [
        'label' => $definition['label'],
        'description' => $definition['description'],
      ];
    }

    $form['debug'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable debugging logs'),
      '#options' => [
        $this->t('No'),
        $this->t('Yes'),
      ],
      '#default_value' => (int) $config->get('debug'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('geoip.geolocation')
      ->set('plugin_id', $form_state->getValue('plugin_id'))
      ->set('debug', $form_state->getValue('debug'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
