<?php

namespace Drupal\covid_dashboard\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Covid dashboard data point edit forms.
 *
 * @ingroup covid_dashboard
 */
class CovidDashboardDataPointForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\covid_dashboard\Entity\CovidDashboardDataPoint $entity */
    $form = parent::buildForm($form, $form_state);

    $form['#attached']['library'][] = 'covid_dashboard/form';

    $main = [
      '#type' => 'fieldset',
      '#title' => $this->t('Main Campus, ATC, ESC'),
      'students' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Students'),
        $form['main_student_positive'],
        $form['main_student_tested'],
        $form['main_student_tested_3'],
        $form['main_student_recovered'],
        $form['main_student_deaths'],
      ],
      'employees' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Empoyees'),
        $form['main_employee_positive'],
        $form['main_employee_tested'],
        $form['main_employee_tested_3'],
        $form['main_employee_recovered'],
        $form['main_employee_deaths'],
      ]
    ];

    $wellsboro = [
      '#type' => 'fieldset',
      '#title' => $this->t('Wellsboro'),
      'students' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Students'),
        $form['wellsboro_student_positive'],
        $form['wellsboro_student_tested'],
        $form['wellsboro_student_tested_3'],
        $form['wellsboro_student_recovered'],
        $form['wellsboro_student_deaths'],
      ],
      'employees' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Empoyees'),
        $form['wellsboro_employee_positive'],
        $form['wellsboro_employee_tested'],
        $form['wellsboro_employee_tested_3'],
        $form['wellsboro_employee_recovered'],
        $form['wellsboro_employee_deaths'],
      ]
    ];

    $form['main_student_positive'] = $main;
    $form['main_student_tested'] = $wellsboro;
    
    unset($form['main_student_recovered']);
    unset($form['main_student_deaths']);
    unset($form['main_employee_positive']);
    unset($form['main_employee_tested']);
    unset($form['main_employee_recovered']);
    unset($form['main_employee_deaths']);
    unset($form['wellsboro_student_positive']);
    unset($form['wellsboro_student_tested']);
    unset($form['wellsboro_student_recovered']);
    unset($form['wellsboro_student_deaths']);
    unset($form['wellsboro_employee_positive']);
    unset($form['wellsboro_employee_tested']);
    unset($form['wellsboro_employee_recovered']);
    unset($form['wellsboro_employee_deaths']);
    unset($form['main_student_tested_3']);
    unset($form['main_employee_tested_3']);
    unset($form['wellsboro_student_tested_3']);
    unset($form['wellsboro_employee_tested_3']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Covid dashboard data point.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Covid dashboard data point.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.covid_dashboard_data_point.canonical', ['covid_dashboard_data_point' => $entity->id()]);
  }

}
