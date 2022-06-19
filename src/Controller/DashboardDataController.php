<?php
 
namespace Drupal\covid_dashboard\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;

use Drupal\covid_dashboard\Entity\CovidDashboardDataPoint;

class DashboardDataController {
  public function renderJson() {
    $data = ['data' => $this->getAllData()];
    
    $data['#cache'] = [
      'max-age' => 3600, 
      'contexts' => [
        'url',
      ],
    ];

    $response = new CacheableJsonResponse($data);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));
    
    return $response;
  }

  private function getAllData(){
    //QUERY
    $query = \Drupal::entityQuery('covid_dashboard_data_point');
    $query->sort('date', ASC);
    $query->condition('status', 1);
    $data_point_ids = $query->execute();

    //RESULT
    $all_data_points = [];
    
    //LOAD UP ALL DATAPOINTS INTO RESULT
    foreach($data_point_ids as $did){
      $data_point = CovidDashboardDataPoint::load($did);
      //$data_point->toArray(); way too verbose

      $date = $data_point->date->value;

      $total_positive += $data_point->main_student_positive->value + $data_point->main_employee_positive->value + $data_point->wellsboro_student_positive->value + $data_point->wellsboro_employee_positive->value ;
      $total_tested += $data_point->main_student_tested->value + $data_point->main_employee_tested->value + $data_point->wellsboro_student_tested->value + $data_point->wellsboro_employee_tested->value ;
      $total_tested_3 += $data_point->main_student_tested_3->value + $data_point->main_employee_tested_3->value + $data_point->wellsboro_student_tested_3->value + $data_point->wellsboro_employee_tested_3->value ;
      $total_recovered += $data_point->main_student_recovered->value + $data_point->main_employee_recovered->value + $data_point->wellsboro_student_recovered->value + $data_point->wellsboro_employee_recovered->value ;
      $total_deaths += $data_point->main_student_deaths->value + $data_point->main_employee_deaths->value + $data_point->wellsboro_student_deaths->value + $data_point->wellsboro_employee_deaths->value ;

      array_push($all_data_points, [$date, $total_positive, $total_tested, $total_tested_3, $total_recovered, $total_deaths]);
    }

    return $all_data_points;
  }


}