<?php

namespace Drupal\cost_estimator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

//use Drupal\cost_estimator\Entity\CovidDashboardDataPoint;

/**
 * Provides a 'Cost Estimator' Block.
 *
 * @Block(
 *   id = "cost_estimator",
 *   admin_label = @Translation("Cost Estimator"),
 *   category = @Translation("Custom"),
 * )
 */
class CostEstimatorBlock extends BlockBase {
  
  private $SLATE_ID = NULL;//Test id: https://www.pct.edu/cost-estimator?name=402eb698-654a-4bca-8a3d-9db0416f36f5

  public function build() {
    //$query = \Drupal::request()->query->get('name');
    $this->SLATE_ID = \Drupal::request()->get('name');
    //var_dump($this->SLATE_ID);

    //$name = \Drupal::request()->request->get('name'); // form param
    //Load major and state from slate
    $major = 'UD';
    $state = NULL;
    $info = $this->getProspectInfo($this->SLATE_ID);
    
    //var_dump($info);
    if(isset($info)){
      $major = $info['major'];
      
      //Some majors are not actually majors...
      $major_code_exceptions = [
        'PCA' => 'PHA',
      ];

      //var_dump($major);
      if(array_key_exists($major, $major_code_exceptions)){
        $major = $major_code_exceptions[$major];
        //echo('Major exception found');
        //var_dump($major);
      }

      $state = $info['state'];
    }

    $cost_estimate_form = \Drupal::formBuilder()->getForm('Drupal\cost_estimator\Form\CostEstimatorForm', $major, $state);
    //var_dump($cost_estimate_form);

    $build = [
      '#cache' =>[
        'contexts' => ['url'],//can have a slate application guid in it
      ],
      '#attached' => [
        'library' => [
          'cost_estimator/cost_estimator.css-js',
        ]
      ],
      '#theme' => 'cost_estimator',
      '#cost_estimate_form' => $cost_estimate_form,
      '#slate_id' => $this->SLATE_ID,
      '#aid' => $this->getAid($this->SLATE_ID),
      '#major' => $major,
      '#state' => $state,
    ];

  	return $build;
  }

  public function getCacheMaxAge() {
    return 8800;
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
    return [];
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
    return Cache::mergeTags(parent::getCacheTags(), ['cost_estimator']);
  }

  /**
   * 
   */
  public function getCacheContexts() {
     return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
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