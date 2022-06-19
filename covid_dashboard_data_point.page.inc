<?php

/**
 * @file
 * Contains covid_dashboard_data_point.page.inc.
 *
 * Page callback for Covid dashboard data point entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Covid dashboard data point templates.
 *
 * Default template: covid_dashboard_data_point.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_covid_dashboard_data_point(array &$variables) {
  // Fetch CovidDashboardDataPoint Entity Object.
  $covid_dashboard_data_point = $variables['elements']['#covid_dashboard_data_point'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
