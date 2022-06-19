<?php

namespace Drupal\covid_dashboard\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Defines the Covid dashboard data point entity.
 *
 * @ingroup covid_dashboard
 *
 * @ContentEntityType(
 *   id = "covid_dashboard_data_point",
 *   label = @Translation("Covid dashboard data point"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\covid_dashboard\CovidDashboardDataPointListBuilder",
 *     "views_data" = "Drupal\covid_dashboard\Entity\CovidDashboardDataPointViewsData",
 *     "translation" = "Drupal\covid_dashboard\CovidDashboardDataPointTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\covid_dashboard\Form\CovidDashboardDataPointForm",
 *       "add" = "Drupal\covid_dashboard\Form\CovidDashboardDataPointForm",
 *       "edit" = "Drupal\covid_dashboard\Form\CovidDashboardDataPointForm",
 *       "delete" = "Drupal\covid_dashboard\Form\CovidDashboardDataPointDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\covid_dashboard\CovidDashboardDataPointHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\covid_dashboard\CovidDashboardDataPointAccessControlHandler",
 *   },
 *   base_table = "covid_dashboard_data_point",
 *   data_table = "covid_dashboard_data_point_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer covid dashboard data point entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/covid_dashboard_data_point/{covid_dashboard_data_point}",
 *     "add-form" = "/admin/content/covid_dashboard_data_point/add",
 *     "edit-form" = "/admin/content/covid_dashboard_data_point/{covid_dashboard_data_point}/edit",
 *     "delete-form" = "/admin/content/covid_dashboard_data_point/{covid_dashboard_data_point}/delete",
 *     "collection" = "/admin/content/covid_dashboard_data_point",
 *   },
 *   field_ui_base_route = "covid_dashboard_data_point.settings"
 * )
 */
class CovidDashboardDataPoint extends ContentEntityBase implements CovidDashboardDataPointInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Covid dashboard data point entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('Entry for ' . DrupalDateTime::createFromTimestamp(time())->format('m-d-Y'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -19,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -19,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);
    
    $fields['date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setDescription(t('The date the data is for.'))
      ->setSettings([
        'datetime_type' => 'date',
      ])
      ->setDefaultValueCallback('covid_dashboard_get_current_datetime')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => -18,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -18,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    /**
     * 
     * Main Campus, ATC, ESC
     * 
     */
    $fields['main_student_positive'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Positive'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -17,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -17,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_student_tested'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -16,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -16,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_student_tested_3'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested (ext)'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -16,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -16,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_student_recovered'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Recovered'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -15,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_student_deaths'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Deaths'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -14,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -14,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_employee_positive'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Positive'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -13,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -13,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_employee_tested'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -12,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -12,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_employee_tested_3'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested (ext)'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -12,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -12,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_employee_recovered'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Recovered'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -11,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['main_employee_deaths'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Deaths'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    /**
     * 
     * WELSBORO
     * 
     */
    $fields['wellsboro_student_positive'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Positive'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -9,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_student_tested'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -8,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_student_tested_3'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested (ext)'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -8,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_student_recovered'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Recovered'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -7,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_student_deaths'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Deaths'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_employee_positive'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Positive'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_employee_tested'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_employee_tested_3'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tested (ext)'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_employee_recovered'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Recovered'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['wellsboro_employee_deaths'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Deaths'))
      ->setSettings([
        'min' => 0,
        'max' => 65353,
        'unsigned' => TRUE,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    
    $fields['percent_vaccinated'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Percent Vaccinated'))
      ->setSettings([
        'precision' => 10,
        'scale' => 2,
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Covid dashboard data point entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => -1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Covid dashboard data point is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 0,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
