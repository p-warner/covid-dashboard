<?php

namespace Drupal\covid_dashboard;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Covid dashboard data point entities.
 *
 * @ingroup covid_dashboard
 */
class CovidDashboardDataPointListBuilder extends EntityListBuilder {

  /*
  public function load() {

    $entity_query = \Drupal::service('entity.query')->get('covid_dashboard_data_point');
    $header = $this->buildHeader();

    $entity_query->pager(100);
    $entity_query->tableSort($header);

    $uids = $entity_query->execute();

    return $this->storage->loadMultiple($uids);
  }
  */

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    //$header['id'] = $this->t('Covid dashboard data point ID');
    //$header['name'] = $this->t('Name');
    $header['date'] = $this->t('Date');
    
    $header['percent_vaccinated'] = [
      'data' => $this->t('Percent Vaccinated'),
      'field' => 'percent_vaccinated',
      'specifier' => 'percent_vaccinated',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['field_active_cases'] = $this->t('Total Active Cases');
    $header['data_main_student'] = $this->t('Main, Student<br>(pos, test, test(e), rec, dead)');
    $header['data_main_employee'] = $this->t('Main, Employee<br>(pos, test, test(e), rec, dead)');
    $header['data_wellsboro_student'] = $this->t('Wellsboro Student<br>(pos, test, test(e), rec, dead)');
    $header['data_wellsboro_employee'] = $this->t('Wellsboro Employee<br>(pos, test, test(e), rec, dead)');
    
    
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\covid_dashboard\Entity\CovidDashboardDataPoint $entity */
    //$row['id'] = $entity->id();
    /*
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.covid_dashboard_data_point.edit_form',
      ['covid_dashboard_data_point' => $entity->id()]
    );
    */

    $row['date'] = $entity->date->value;

    $row['percent_vaccinated'] = 
      $entity->percent_vaccinated->value.'';
    $row['field_active_cases'] = 
      $entity->field_active_cases->value.'';
    $row['data_main_student'] = 
      $entity->main_student_positive->value.', '.
      $entity->main_student_tested->value.', '.
      ($entity->main_student_tested_3->value>0?$entity->main_student_tested_3->value:0).', '.
      $entity->main_student_recovered->value.', '.
      $entity->main_student_deaths->value;
    $row['data_main_employee'] = 
      $entity->main_employee_positive->value.', '.
      $entity->main_employee_tested->value.', '.
      ($entity->main_employee_tested_3->value>0?$entity->main_employee_tested_3->value:0).', '.
      $entity->main_employee_recovered->value.', '.
      $entity->main_employee_deaths->value;
    $row['data_wellsboro_student'] = 
      $entity->wellsboro_student_positive->value.', '.
      $entity->wellsboro_student_tested->value.', '.
      ($entity->wellsboro_student_tested_3->value>0?$entity->wellsboro_student_tested_3->value:0).', '.
      $entity->wellsboro_student_recovered->value.', '.
      $entity->wellsboro_student_deaths->value;
    $row['data_wellsboro_employee'] = 
      $entity->wellsboro_employee_positive->value.', '.
      $entity->wellsboro_employee_tested->value.', '.
      ($entity->wellsboro_employee_tested_3->value>0?$entity->wellsboro_employee_tested_3->value:0).', '.
      $entity->wellsboro_employee_recovered->value.', '.
      $entity->wellsboro_employee_deaths->value;
    
    return $row + parent::buildRow($entity);
  }

}
