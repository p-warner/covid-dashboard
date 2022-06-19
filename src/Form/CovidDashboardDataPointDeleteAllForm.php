<?php

namespace Drupal\covid_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\covid_dashboard\Entity\CovidDashboardDataPoint;

/**
 * Class CovidDashboardDataPointDeleteAllForm.
 *
 * @ingroup covid_dashboard
 */
class CovidDashboardDataPointDeleteAllForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'coviddashboarddatapoint_deleteallform';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') != 'confirm') {
      $form_state->setErrorByName('confirm', $this->t('You must enter "confirm" to remove all data points.'));
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    //QUERY
    $query = \Drupal::entityQuery('covid_dashboard_data_point');
    $query->sort('date', ASC);
    $query->condition('status', 1);
    $data_point_ids = $query->execute();
    
    //LOAD UP ALL DATAPOINTS INTO RESULT
    foreach($data_point_ids as $did){
      $data_point = CovidDashboardDataPoint::load($did);
      //$data_point->toArray(); way too verbose

      $data_point->delete();
    }

    \Drupal::messenger()->addMessage($this->t('Removed all data points.'));
  }

  /**
   * Defines the settings form for Covid dashboard data point entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['coviddashboarddatapoint_deleteallform']['#markup'] = '<p>This will remove ALL data points.</p>';
    
    $form['confirm'] = array(
        '#type' => 'textfield',
        '#title' => $this
          ->t('Confirm'),
        '#default_value' => '',
        '#size' => 30,
        '#maxlength' => 16,
        //'#pattern' => 'some-prefix-[a-z]+',
        '#required' => TRUE,
      );

    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Submit',
      );

    return $form;
  }

}
