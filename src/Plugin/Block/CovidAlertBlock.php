<?php

namespace Drupal\covid_dashboard\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;


/**
 * Provides a 'COVID-19 Alert' Block.
 *
 * @Block(
 *   id = "covid_alert_3",
 *   admin_label = @Translation("Covid Alert (3)"),
 *   category = @Translation("Custom"),
 * )
 */
class CovidAlertBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'block_covid_alert_3',
      '#html' => $this->configuration['html'],
    ];
  }

  /**
   * {@inheritdoc}
   *
   * This method sets the block default configuration. This configuration
   * determines the block's behavior when a block is initially placed in a
   * region. Default values for the block configuration form should be added to
   * the configuration array. System default configurations are assembled in
   * BlockBase::__construct() e.g. cache setting and block title visibility.
   *
   * @see \Drupal\block\BlockBase::__construct()
   */
  public function defaultConfiguration() {
    return [
      'html' => '',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * This method defines form elements for custom block configuration. Standard
   * block configuration fields are added by BlockBase::buildConfigurationForm()
   * (block title and title visibility) and BlockFormController::form() (block
   * visibility settings).
   *
   * @see \Drupal\block\BlockBase::buildConfigurationForm()
   * @see \Drupal\block\BlockFormController::form()
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['body_html'] = array(
      '#type' => 'text_format',
      '#title' => 'Block HTML',
      '#format' => 'full_html',
      '#default_value' => $this->configuration['html'],
      '#required' => TRUE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method processes the blockForm() form fields when the block
   * configuration form is submitted.
   *
   * The blockValidate() method can be used to validate the form submission.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['html'] = $form_state->getValue('body_html')['value'];
  }

  public function getCacheMaxAge() {
    return 86400;
  }

  /**
   * Cache tag of {entity}_list will be invalidated when a new {entity} is added. This will inform Varnish
   * to get a fresh copy once max-age has expired.
   */
  public function getCacheTags() {
    return parent::getCacheTags();
    //return Cache::mergeTags(parent::getCacheTags(), ['covid_dashboard_data_point_list']);
  }

  /**
   * 
   */
  public function getCacheContexts() {
     return Cache::mergeContexts(parent::getCacheContexts(), ['ip']);
  }

}