<?php
namespace Drupal\cost\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * .
 */
class CostController extends ControllerBase {
  /**
   * Constructor
   * 
   * Load Acquia environment variables.
   */
  public function __construct(){
    if (file_exists('/var/www/site-php')) {
      require '/var/www/site-php/pct/pct-settings.inc';
    }
  }

  /**
   * TODO: There are 2 calls to slate... I can't figure out how to join 
   * financial aid -> person -> application to get everything in one query 
   * within slate.
   * 
   * @param $slate_id is application GUID.
   * {@inheritdoc}
   */
  public function content($slate_id = NULL, Request $request) {
    //$name = \Drupal::request()->request->get('name'); // form param
    //Load major and state from slate
    $major = 'UD';
    $state = NULL;
    $info = $this->getProspectInfo($slate_id);
    
    if(isset($info)){
      $major = $info['major'];
      $state = $info['state'];
    }

    $build = [
      '#cache' =>[
        'contexts' => ['url'],//can have a slate application guid in it
      ],
      '#theme' => 'cost',
      '#form' => \Drupal::formBuilder()->getForm('Drupal\cost\Form\CostForm', $major, $state),
      '#slate_id' => $slate_id,
      '#aid' => $this->getAid($slate_id),
      '#major' => $major,
      '#state' => $state,
    ];

  	return $build;
  }

  /**
   * Slate web service for a query on finaid. SQL is saved under user pwarner in Slate.
   * 
   * @param {String} $id a slate application GUID. E.g. 402eb698-654a-4bca-8a3d-9db0416f36f5
   */
  private function getAid($id){
    if(!$id){
      return NULL;
    }

    $json_result = file_get_contents('https://admissions.pct.edu/manage/query/run?id=f9f7c3a3-1619-48ca-96b2-eb20b0f60999&h=a88dd928-82b9-1093-7f1c-0fda6121bfb1&cmd=service&output=json&ref='.$id);
    $json_array = json_decode($json_result, true);
    $aid = [];

    foreach($json_array['row'] as $item) {
      array_push($aid, $item);
    }

    return $aid;
  }

  /**
   * Slate web service for a query on person & appliation.SQL is saved under user pwarner in Slate.
   * 
   * @param {String} $id a slate application GUID. E.g. e9428a95-e040-48ca-b1cf-8d1e5c695254
   */
  private function getProspectInfo($id){
    if(!$id){
      return NULL;
    }

    $json_result = file_get_contents('https://admissions.pct.edu/manage/query/run?id=69ecfeef-fd46-480e-ac24-c12c9abfb71e&h=21303fe4-6bc9-7631-fcec-5da758ab9aee&cmd=service&output=json&ref='.$id);
    $json_array = json_decode($json_result, true);
    $info = [];

    //This is odd. We should only ever get 1 row.
    foreach($json_array['row'] as $item) {
      $info['major'] = $item['major'];
      $info['state'] = $item['region'];
    }
    
    return $info;
  }
}
