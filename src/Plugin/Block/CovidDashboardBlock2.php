<?php

namespace Drupal\covid_dashboard\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

use Drupal\covid_dashboard\Entity\CovidDashboardDataPoint;


/**
 * Provides a 'COVID-19 Dashboard' Block.
 *
 * @Block(
 *   id = "covid_dashboard_2",
 *   admin_label = @Translation("Covid Dashboard 2"),
 *   category = @Translation("Custom"),
 * )
 */
class CovidDashboardBlock2 extends BlockBase {
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
    $total_active = 0;

    $all_data = [];

    //ONLY SHOW DATA BETWEEN THESE TWO DATES
    $start = '2021-07-01 00:00:00';//waiting on pace.
    $end = 'now';

    $data_point_ids = $this->getDataBetween($start, $end);

    $first_entry = true;
    //This loop goes from the most recent entry to the oldest.
    foreach($data_point_ids as $did){
      $data_point = CovidDashboardDataPoint::load($did);
      
      /* Load up total_active and vaccinated with the first entry only.
       * The active field is not reported for each day, only
       * the total active cases is reported.
       */
      if($first_entry){
        $total_active = $data_point->field_active_cases->value;
        $total_vaccinated = $data_point->percent_vaccinated->value;
        $first_entry = false;
      }

      $total_positive += 
        $data_point->main_student_positive->value + 
        $data_point->main_employee_positive->value + 
        $data_point->wellsboro_student_positive->value + 
        $data_point->wellsboro_employee_positive->value ;
      
      $total_tested += 
        $data_point->main_student_tested->value + 
        $data_point->main_employee_tested->value + 
        $data_point->wellsboro_student_tested->value + 
        $data_point->wellsboro_employee_tested->value ;
      
      $total_tested_3 += 
        $data_point->main_student_tested_3->value + 
        $data_point->main_employee_tested_3->value + 
        $data_point->wellsboro_student_tested_3->value + 
        $data_point->wellsboro_employee_tested_3->value ;
      
      $total_recovered += 
        $data_point->main_student_recovered->value + 
        $data_point->main_employee_recovered->value + 
        $data_point->wellsboro_student_recovered->value + 
        $data_point->wellsboro_employee_recovered->value ;
      
      $total_deaths += 
        $data_point->main_student_deaths->value + 
        $data_point->main_employee_deaths->value + 
        $data_point->wellsboro_student_deaths->value + 
        $data_point->wellsboro_employee_deaths->value ;

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
      '#theme' => 'block_covid_dashboard_2',
      '#total_positive' => $total_positive,
      '#total_tested' => $total_tested,
      '#total_tested_3' => $total_tested_3,
      '#total_recovered' => $total_recovered,
      '#total_deaths' => $total_deaths,
      '#total_active' => $total_active,
      '#total_vaccinated' => $total_vaccinated,
      '#number_data_points' => sizeof($data_point_ids),
      '#all_data' => $all_data,
      '#last_updated' => $last_updated,
    ];
  }

  /**
   * Returns IDs of datapoints between two dates in reverse chronological order.
   */
  private function getDataBetween($start_str, $end_str){
    $timezone = date_default_timezone_get();

    $start = new \DateTime($start_str, new \DateTimeZone($timezone));
    $start->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $start = DrupalDateTime::createFromDateTime($start);
    
    $end = new \DateTime($end_str, new \DateTimezone($timezone));
    $end->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $end = DrupalDateTime::createFromDateTime($end);

    $query = \Drupal::entityQuery('covid_dashboard_data_point');
    $query->sort('date', DESC);
    $query
      ->condition('date', $start->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '>=')
      ->condition('date', $end->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '<=')
      ->condition('status', 1);
    
    return $query->execute();
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