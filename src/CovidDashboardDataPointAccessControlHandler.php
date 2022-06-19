<?php

namespace Drupal\covid_dashboard;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Covid dashboard data point entity.
 *
 * @see \Drupal\covid_dashboard\Entity\CovidDashboardDataPoint.
 */
class CovidDashboardDataPointAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\covid_dashboard\Entity\CovidDashboardDataPointInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished covid dashboard data point entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published covid dashboard data point entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit covid dashboard data point entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete covid dashboard data point entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add covid dashboard data point entities');
  }


}
