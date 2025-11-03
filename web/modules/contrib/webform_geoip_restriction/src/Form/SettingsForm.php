<?php

declare(strict_types = 1);

namespace Drupal\webform_geoip_restriction\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Webform GeoIP Restriction settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'webform_geoip_restriction_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['webform_geoip_restriction.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['maxmind_account'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maxmind Account ID'),
      '#default_value' => $this->config('webform_geoip_restriction.settings')->get('maxmind_account'),
    ];
    $form['maxmind_license'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maxmind License Key'),
      '#default_value' => $this->config('webform_geoip_restriction.settings')->get('maxmind_license'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('webform_geoip_restriction.settings')
      ->set('maxmind_account', $form_state->getValue('maxmind_account'))
      ->set('maxmind_license', $form_state->getValue('maxmind_license'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
