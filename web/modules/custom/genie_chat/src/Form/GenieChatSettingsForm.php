<?php

namespace Drupal\genie_chat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Genie Chat settings.
 */
class GenieChatSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['genie_chat.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'genie_chat_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('genie_chat.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Genie Chat'),
      '#default_value' => $config->get('enabled') ?? FALSE,
      '#description' => $this->t('Check this box to enable the Genie Chat widget on your site.'),
    ];

    $form['bot_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bot Name'),
      '#default_value' => $config->get('bot_name') ?? 'My chat Bot',
      '#description' => $this->t('The name of the chat bot.'),
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $config->get('token') ?? '',
      '#description' => $this->t('The authentication token for the Genie service.'),
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['bot_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bot Title'),
      '#default_value' => $config->get('bot_title') ?? 'Chat Assistant',
      '#description' => $this->t('The title displayed for the chat bot.'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['system_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('System Message'),
      '#default_value' => $config->get('system_message') ?? 'Hello! How can I help you today?',
      '#description' => $this->t('The system message or initial greeting for the chat bot.'),
      '#rows' => 3,
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['base_logo'] = [
      '#type' => 'url',
      '#title' => $this->t('Base Logo URL'),
      '#default_value' => $config->get('base_logo') ?? '',
      '#description' => $this->t('URL to the site logo to be displayed in the chat widget.'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['appearance'] = [
      '#type' => 'details',
      '#title' => $this->t('Appearance Settings'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['appearance']['primary_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Primary Color'),
      '#default_value' => $config->get('primary_color') ?? '#009650',
      '#description' => $this->t('The primary color for the chat widget.'),
    ];

    $form['appearance']['secondary_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Secondary Color'),
      '#default_value' => $config->get('secondary_color') ?? '#E4F6EE',
      '#description' => $this->t('The secondary color for the chat widget.'),
    ];

    $form['appearance']['text_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Text Color'),
      '#default_value' => $config->get('text_color') ?? '#000000',
      '#description' => $this->t('The color for primary text in the chat widget.'),
    ];

    $form['appearance']['secondary_text_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Secondary Text Color'),
      '#default_value' => $config->get('secondary_text_color') ?? '#ffffff',
      '#description' => $this->t('The color for secondary text in the chat widget.'),
    ];

    $form['appearance']['chat_location'] = [
      '#type' => 'select',
      '#title' => $this->t('Chat Location'),
      '#default_value' => $config->get('chat_location') ?? 'right',
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#description' => $this->t('The position of the chat widget on the page.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('genie_chat.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('bot_name', $form_state->getValue('bot_name'))
      ->set('token', $form_state->getValue('token'))
      ->set('bot_title', $form_state->getValue('bot_title'))
      ->set('system_message', $form_state->getValue('system_message'))
      ->set('base_logo', $form_state->getValue('base_logo'))
      ->set('primary_color', $form_state->getValue('primary_color'))
      ->set('secondary_color', $form_state->getValue('secondary_color'))
      ->set('text_color', $form_state->getValue('text_color'))
      ->set('secondary_text_color', $form_state->getValue('secondary_text_color'))
      ->set('chat_location', $form_state->getValue('chat_location'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}