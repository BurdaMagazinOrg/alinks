<?php

/**
 * @file
 * Contains alink_keyword.page.inc.
 *
 * Page callback for Keyword entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Keyword templates.
 *
 * Default template: alink_keyword.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_alink_keyword(array &$variables) {
  // Fetch Keyword Entity Object.
  $alink_keyword = $variables['elements']['#alink_keyword'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
