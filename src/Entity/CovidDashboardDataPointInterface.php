<?php

namespace Drupal\covid_dashboard\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Covid dashboard data point entities.
 *
 * @ingroup covid_dashboard
 */
interface CovidDashboardDataPointInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Covid dashboard data point name.
   *
   * @return string
   *   Name of the Covid dashboard data point.
   */
  public function getName();

  /**
   * Sets the Covid dashboard data point name.
   *
   * @param string $name
   *   The Covid dashboard data point name.
   *
   * @return \Drupal\covid_dashboard\Entity\CovidDashboardDataPointInterface
   *   The called Covid dashboard data point entity.
   */
  public function setName($name);

  /**
   * Gets the Covid dashboard data point creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Covid dashboard data point.
   */
  public function getCreatedTime();

  /**
   * Sets the Covid dashboard data point creation timestamp.
   *
   * @param int $timestamp
   *   The Covid dashboard data point creation timestamp.
   *
   * @return \Drupal\covid_dashboard\Entity\CovidDashboardDataPointInterface
   *   The called Covid dashboard data point entity.
   */
  public function setCreatedTime($timestamp);

}
