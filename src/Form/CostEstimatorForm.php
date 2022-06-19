<?php
/**
 * @file
 * Contains \Drupal\cost_estimator\Form\CostEstimatorForm.
 */
namespace Drupal\cost_estimator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\cost_estimator\Controller\CostEstimatorDataController;

class CostEstimatorForm extends FormBase {
  private $AID = NULL;

  public function __construct(){
  }

  public function getFormId() {
    return 'cost_estimator_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $major_code = NULL, $resident = NULL) {
    $form['#attached']['library'][] = 'cost_estimator/cost_estimator.css-js';

    //AJAX options used on all form elements.
    $AJAX_SETTINGS = [
      'callback' => array($this, 'stateChanged'),
      //'event' => 'change',
      'progress' => array(
        'type' => 'throbber',
        'message' => $this->t('Hol up'),
      ),
    ];

    // HTML snippet example
    $form['description'] = ['#type'=>'html_tag','#tag'=>'p','#attributes'=>['class'=>'status-message'], '#value'=>t('')];
    $form['debug'] = ['#type'=>'html_tag','#tag'=>'p','#attributes'=>['class'=>'debug'], '#value'=>t('')];

    $form['major'] = ['#type'=>'html_tag','#tag'=>'div','#attributes'=>['class'=>'mx-auto col-lg-6']];
    $form['major']['major_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Let&rsquo;s start by selecting your academic program.')];
    $form['major']['major_description'] = ['#type'=>'html_tag','#tag'=>'p','#attributes'=>['class'=>'small text-muted'], '#value'=>t('Each program has unique expenses other than tuition:&nbsp;books, tools, etc.')];
    $form['major']['major_select'] = [
      '#type' => 'select',
      '#title' => $this->t(''),
      '#attributes'=>['data-major-select'=>''],
      '#ajax' => $AJAX_SETTINGS,
      '#options' => $this->arrayToOptions(CostEstimatorDataController::getMajors()),
      '#default_value' => $major_code,
      '#required' => FALSE,
    ];

    $form['residency']['residency_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Are you a resident of Pennsylvania?')];
    $form['residency']['residency_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#attributes'=>['data-parent-section-id'=>'section_residency_choose'],
      '#ajax' => $AJAX_SETTINGS,
      '#default_value' => ($resident === 'PA') ? 587 : 839,
      '#options' => [
        587 => $this->t('Yes'),
        839 => $this->t('No'),
      ],
      '#required' => FALSE,
    ];

    $form['international']['international_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Are you an international student planning to study on a student visa?')];
    $form['international']['international_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#attributes'=>['data-parent-section-id'=>'section_international_choose'],
      '#ajax' => $AJAX_SETTINGS,
      '#options' => [
        500 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#required' => FALSE,
    ];

    $form['living']['living_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Do you plan to live on-campus?')];
    $form['living']['living_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#attributes'=>['data-parent-section-id'=>'section_housing_choose'],
      '#ajax' => $AJAX_SETTINGS,
      '#options' => [
        0 => $this->t('Yes'),
        1 => $this->t('No'),
      ],
      '#required' => FALSE,
    ];

    //housing on campus, not automotive
    $form['housing_on']['housing_on_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('What&rsquo;s your housing preference?')];
    $form['housing_on']['housing_on_select'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#attributes'=>[
        'data-housing-select'=>'',
        'data-parent-section-id'=>'section_housing_on_choose'],
      '#ajax' => $AJAX_SETTINGS,
      '#empty_option' => 'Choose a housing option...',
      '#empty_value' => 0,
      '#options' => [
        2678 => '4 students – 1 bedroom apartment',
        3012 => '6 students – 3 bedroom apartment',
        3392 => '2 students – 1 double bedroom',
        3556 => '1 students – 1 single bedroom',
      ],
      '#required' => FALSE,
    ];

    //Housing on campus, not automotive dining options
    $form['meal_housing_on']['meal']['dining_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Which dining plan suits you best?')];
    $form['meal_housing_on']['meal']['meal_housing_on_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#attributes'=>['data-parent-section-id'=>'section_housing_on_meal_choose'],
      '#ajax' => $AJAX_SETTINGS,
      '#options' => [
        2110 => $this->t('$2,110 Board Plan (14 meals per week / $300 flex) per semester'),
        2554 => $this->t('$2,554 Board Plan (19 meals per week / $300 flex) per semester'),
      ],
      '#required' => FALSE,
    ];

    //Housing autmotive dining options
    $form['meal_housing_on_eight_week']['dining']['dining_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Which dining plan suits you best?')];
    $form['meal_housing_on_eight_week']['dining']['meal_housing_on_eight_week_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#ajax' => $AJAX_SETTINGS,
      '#attributes'=>['data-parent-section-id'=>'section_housing_on_eight_week_meal_choose'],
      '#options' => [
        943 => $this->t('$943 Board Plan (10 meals per week / $150 flex) per semester'),
        1055 => $this->t('$1,055 Board Plan (14 meals per week / $150 flex) per semester'),
        1277 => $this->t('$1,277 Board Plan (19 meals per week / $150 flex) per semester'),
      ],
      '#required' => FALSE,
    ];

    $form['meal_housing_off']['housing_off_title'] = ['#type'=>'html_tag','#tag'=>'h4','#attributes'=>['class'=>'h4 w-100'], '#value'=>t('Which dining plan suits you best?')];    
    $form['meal_housing_off']['dining']['meal_housing_off_radio'] = [
      '#type' => 'radios',
      '#title' => $this->t(''),
      '#ajax' => $AJAX_SETTINGS,
      '#attributes'=>['data-parent-section-id'=>'section_housing_off_meal_choose'],
      '#options' => [
        0 => $this->t('No Board Plan'),
        1138 => $this->t('$1,138 Board Plan (5 meals per week / $300 flex) per semester'),
        1886 => $this->t('$1,886 Board Plan (10 meals per week / $300 flex) per semester'),
        2110 => $this->t('$2,110 Board Plan (14 meals per week / $300 flex) per semester'),
        2554 => $this->t('$2,554 Board Plan (19 meals per week / $300 flex) per semester'),
      ],
      '#required' => FALSE,
    ];

    //load initial
    $this->stateChanged($form, $form_state);

    $form['#limit_validation_errors'] = [];
    return $form;
  }

  /**
   * UNUSED. The form is never submitted.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return NULL;
  }

  /**
   * UNUSED. Actually update the databse on form submit?
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
  }

  /**
   * Called when the state of the form changes.
   */
  public function stateChanged(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $response->addCommand(new InvokeCommand(NULL, 'refresh_cost', [
        [
          'itemized_list' => CostEstimatorDataController::getCost($form['major']['major_select']['#value']), 
          'aid' => $this->AID,
        ],
      ]
    ));

    $response->addCommand(new InvokeCommand(NULL, 'refresh_section_visibility', []));

    return $response;
  }
  
  /**
	 * Give array of majors and codes.
	 */
	private function arrayToOptions($array){
    $a = ['UD' => 'Undecided'];

    foreach($array as $key => $item){
      foreach($item as $item1){
        $a[$key][$item1->pgm] = $item1->pgmname . '' . $item1->degree . '';
      }
    }

		return $a;
	}
}
