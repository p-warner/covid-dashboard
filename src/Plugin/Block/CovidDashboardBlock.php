<?php

namespace Drupal\covid_dashboard\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

use Drupal\covid_dashboard\Entity\CovidDashboardDataPoint;


/**
 * Provides a 'COVID-19 Dashboard' Block.
 *
 * @Block(
 *   id = "covid_dashboard",
 *   admin_label = @Translation("Covid Dashboard"),
 *   category = @Translation("Custom"),
 * )
 */
class CovidDashboardBlock extends BlockBase {
    
  /**
   * {@inheritdoc}
   */
  public function build() {
    $last_updated = NULL;
    $recent = FALSE;

    $total_positive = 0;
    $total_tested = 0;
    $total_tested_3 = 0;
    $total_recovered = 0;
    $total_deaths = 0;

    $all_data = [];

    $query = \Drupal::entityQuery('covid_dashboard_data_point');
    $query->sort('date', DESC);
    $query->condition('status', 1);
    $data_point_ids = $query->execute();
    
    foreach($data_point_ids as $did){
      $data_point = CovidDashboardDataPoint::load($did);
      
      $total_positive += $data_point->main_student_positive->value + $data_point->main_employee_positive->value + $data_point->wellsboro_student_positive->value + $data_point->wellsboro_employee_positive->value ;
      $total_tested += $data_point->main_student_tested->value + $data_point->main_employee_tested->value + $data_point->wellsboro_student_tested->value + $data_point->wellsboro_employee_tested->value ;
      $total_tested_3 += $data_point->main_student_tested_3->value + $data_point->main_employee_tested_3->value + $data_point->wellsboro_student_tested_3->value + $data_point->wellsboro_employee_tested_3->value ;
      $total_recovered += $data_point->main_student_recovered->value + $data_point->main_employee_recovered->value + $data_point->wellsboro_student_recovered->value + $data_point->wellsboro_employee_recovered->value ;
      $total_deaths += $data_point->main_student_deaths->value + $data_point->main_employee_deaths->value + $data_point->wellsboro_student_deaths->value + $data_point->wellsboro_employee_deaths->value ;

      array_push($all_data, $data_point);
    }



    /**
     * LAST UPDATED
     */
    $data_point = CovidDashboardDataPoint::load($data_point_ids[array_key_first($data_point_ids)]);
    $date_formatter = \Drupal::service('date.formatter');
    $last_updated = $date_formatter->formatDiff($data_point->created->value, \Drupal::time()->getRequestTime(), [
        'granularity' => 1,
        'return_as_object' => TRUE,
      ]);
    

    return [
      '#theme' => 'block_covid_dashboard',
      '#total_positive' => $total_positive,
      '#total_tested' => $total_tested,
      '#total_tested_3' => $total_tested,
      '#total_recovered' => $total_recovered,
      '#total_deaths' => $total_deaths,
      '#number_data_points' => sizeof($data_point_ids),
      '#all_data' => $all_data,
      '#last_updated' => $last_updated,
    ];
  }

  public function getCacheMaxAge() {
    return 60;
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
      'total_positive' => 0,
      'total_tested' => 0,
      'total_tested_3' => 0,
      'total_recovered' => 0,
      'total_deaths' => 0,
      'number_data_points' => 0,
      'all_data' => [],
      'last_updated' => 0,
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
    /*
    $node = Node::load($this->configuration['link']);
    $form['link'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Link'),
      '#description' => $this->t('Optional link to more information. This will enable the badge "Updated x days ago if x < 7."'),
      '#target_type' => 'node',
      '#default_value' => $node
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('The notice message. Limited to 128 characters, if theres more consider linking to a page.'),
      '#required' => TRUE,
      '#maxlength' => 128,
      '#default_value' => $this->configuration['description']
    ];
    return $form;
    */
    return [];
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
    /*
    $this->configuration['link'] = $form_state->getValue('link');
    $this->configuration['description'] = $form_state->getValue('description');
    */
  }

  /**
   * Cache tag of {entity}_list will be invalidated when a new {entity} is added. This will inform Varnish
   * to get a fresh copy once max-age has expired.
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['covid_dashboard_data_point_list']);
  }

  /**
   * 
   */
  public function getCacheContexts() {
     return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}