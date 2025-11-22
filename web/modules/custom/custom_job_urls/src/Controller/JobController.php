<?php

namespace Drupal\custom_job_urls\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for custom job URLs.
 */
class JobController extends ControllerBase {

  /**
   * Job listing page - renders the view directly with filters applied.
   * This method now handles both "/jobsearch/..." and "/...-jobs-in-..." URLs.
   */
  public function jobListing($job_category, $location = NULL) {
    // DEBUG: Log the incoming parameters
    \Drupal::logger('custom_job_urls')->info('JobListing called with job_category: @job_category, location: @location', [
      '@job_category' => $job_category,
      '@location' => $location ?: 'NULL',
    ]);

    // Convert URL segments (e.g., "information-technology") to a readable format.
    $job_name = str_replace('-', ' ', $job_category);
    $job_name = ucwords(strtolower($job_name));

    // Prepare exposed filters input.
    $exposed_input = [];
    $location_name = '';

    // DEBUG: Log the converted job name
    \Drupal::logger('custom_job_urls')->info('Converted job_name: @job_name', [
      '@job_name' => $job_name,
    ]);

    // Get job term ID and format the filter value.
    $job_term_id = $this->getTermIdByName($job_name, 'category');
    if (!$job_term_id) {
      $job_term_id = $this->findTermByPartialMatch($job_name, 'category');
    }

    // DEBUG: Log the job term ID
    \Drupal::logger('custom_job_urls')->info('Job term ID: @job_term_id', [
      '@job_term_id' => $job_term_id ?: 'NULL',
    ]);

    if ($job_term_id) {
      // Format the filter value exactly as your view's exposed filter expects it.
      // Example: "Information Technolgy (4)"
      $exposed_input['job'] = $job_name . ' (' . $job_term_id . ')';
    }

    // Handle location if provided.
    if ($location) {
      $location_name = str_replace('-', ' ', $location);
      $location_name = ucwords(strtolower($location_name));

      // DEBUG: Log the converted location name
      \Drupal::logger('custom_job_urls')->info('Converted location_name: @location_name', [
        '@location_name' => $location_name,
      ]);

      // IMPORTANT: Replace 'location' with your actual location vocabulary machine name.
      $location_term_id = $this->getTermIdByName($location_name, 'location');

      // DEBUG: Log the location term ID
      \Drupal::logger('custom_job_urls')->info('Location term ID: @location_term_id', [
        '@location_term_id' => $location_term_id ?: 'NULL',
      ]);

      if ($location_term_id) {
        // Format the filter value. Example: "Islamabad (3)"
        $exposed_input['location'] = $location_name . ' (' . $location_term_id . ')';
      }
    }

    // DEBUG: Log the final exposed input
    \Drupal::logger('custom_job_urls')->info('Exposed input: @exposed_input', [
      '@exposed_input' => print_r($exposed_input, TRUE),
    ]);

    // Load and render the view.
    $view_storage = \Drupal::entityTypeManager()->getStorage('view');
    $view_entity = $view_storage->load('jobs');

    if (!$view_entity) {
      \Drupal::logger('custom_job_urls')->error('The "jobs" view was not found.');
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('The "jobs" view was not found.');
    }

    $view = \Drupal::service('views.executable')->get($view_entity);

    // DEBUG: Check available displays
    $displays = $view_entity->get('display');
    $available_displays = array_keys($displays);
    \Drupal::logger('custom_job_urls')->info('Available displays: @displays', [
      '@displays' => implode(', ', $available_displays),
    ]);

    // Set the display ID. Check your view for the correct ID (e.g., 'page_1', 'default').
    $display_id = 'page_1';
    if (!in_array($display_id, $available_displays)) {
      $display_id = 'default';
      \Drupal::logger('custom_job_urls')->warning('page_1 display not found, using default');
    }

    $view->setDisplay($display_id);

    // Apply the exposed filters.
    $view->setExposedInput($exposed_input);

    // Execute the view.
    $view->preExecute();
    $view->execute();

    // DEBUG: Check view results
    \Drupal::logger('custom_job_urls')->info('View executed. Result count: @count', [
      '@count' => count($view->result),
    ]);

    // Build the render array.
    $build = [];

    // Add debug info for administrators
    if (\Drupal::service('current_user')->hasPermission('administer site configuration')) {
      $build['debug'] = [
        '#markup' => '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">
          <strong>Debug Info:</strong><br>
          Raw job_category: ' . htmlspecialchars($job_category) . '<br>
          Converted job_name: ' . htmlspecialchars($job_name) . '<br>
          Job term ID: ' . ($job_term_id ?: 'Not found') . '<br>
          Raw location: ' . htmlspecialchars($location ?: 'Not provided') . '<br>
          Converted location_name: ' . htmlspecialchars($location_name ?: 'Not provided') . '<br>
          Location term ID: ' . (isset($location_term_id) ? $location_term_id : 'Not found') . '<br>
          Exposed Input: <pre>' . htmlspecialchars(print_r($exposed_input, TRUE)) . '</pre>
          Display Used: ' . $display_id . '<br>
          Available Displays: ' . implode(', ', $available_displays) . '<br>
          View Results: ' . count($view->result) . '
        </div>',
      ];
    }

    $build['view'] = $view->buildRenderable($display_id, [], FALSE);

    // Set the page title dynamically.
    $title_parts = [];
    if (!empty($job_name)) {
      $title_parts[] = $job_name . ' Jobs';
    }
    if (!empty($location_name)) {
      $title_parts[] = 'in ' . $location_name;
    }

    if (!empty($title_parts)) {
      $build['#title'] = implode(' ', $title_parts);
    }

    // Add cache tags and contexts for proper Drupal caching.
    $build['#cache']['tags'][] = 'taxonomy_term_list:category';
    if (isset($location_term_id)) {
      // Update with your location vocabulary machine name.
      $build['#cache']['tags'][] = 'taxonomy_term_list:location';
    }
    $build['#cache']['contexts'][] = 'url.path';

    return $build;
  }

  /**
   * Get term ID by exact name match.
   */
  private function getTermIdByName($term_name, $vocabulary) {
    \Drupal::logger('custom_job_urls')->info('Searching for exact term: "@term_name" in vocabulary: @vocabulary', [
      '@term_name' => $term_name,
      '@vocabulary' => $vocabulary,
    ]);

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $term_name,
        'vid' => $vocabulary,
      ]);

    if (!empty($terms)) {
      $term = reset($terms);
      \Drupal::logger('custom_job_urls')->info('Found exact match: @name (@id)', [
        '@name' => $term->getName(),
        '@id' => $term->id(),
      ]);
      return $term->id();
    }

    \Drupal::logger('custom_job_urls')->warning('No exact match found for: @term_name', [
      '@term_name' => $term_name,
    ]);
    return NULL;
  }

  /**
   * Find term by partial match (e.g., for typos like "technolgy").
   */
  private function findTermByPartialMatch($search_name, $vocabulary) {
    \Drupal::logger('custom_job_urls')->info('Searching for partial match: "@search_name" in vocabulary: @vocabulary', [
      '@search_name' => $search_name,
      '@vocabulary' => $vocabulary,
    ]);

    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocabulary)
      ->accessCheck(FALSE);

    $tids = $query->execute();

    if (empty($tids)) {
      \Drupal::logger('custom_job_urls')->warning('No terms found in vocabulary: @vocabulary', [
        '@vocabulary' => $vocabulary,
      ]);
      return NULL;
    }

    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);

    // DEBUG: Log all available terms
    $term_names = [];
    foreach ($terms as $term) {
      $term_names[] = $term->getName() . ' (' . $term->id() . ')';
    }
    \Drupal::logger('custom_job_urls')->info('Available terms in @vocabulary: @terms', [
      '@vocabulary' => $vocabulary,
      '@terms' => implode(', ', $term_names),
    ]);

    $search_lower = strtolower($search_name);
    $best_match = NULL;
    $best_score = 0;

    foreach ($terms as $term) {
      $term_name_lower = strtolower($term->getName());

      // Calculate string similarity.
      $similarity = 0;
      similar_text($search_lower, $term_name_lower, $similarity);

      \Drupal::logger('custom_job_urls')->info('Comparing "@search" with "@term": @similarity% similarity', [
        '@search' => $search_name,
        '@term' => $term->getName(),
        '@similarity' => round($similarity, 2),
      ]);

      // We accept a match if it's over a 70% similarity threshold.
      if ($similarity > $best_score && $similarity > 70) {
        $best_score = $similarity;
        $best_match = $term->id();
      }
    }

    if ($best_match) {
      \Drupal::logger('custom_job_urls')->info('Best partial match found: ID @id with @score% similarity', [
        '@id' => $best_match,
        '@score' => round($best_score, 2),
      ]);
    } else {
      \Drupal::logger('custom_job_urls')->warning('No partial match found above 70% threshold for: @search_name', [
        '@search_name' => $search_name,
      ]);
    }

    return $best_match;
  }

  /**
   * Test route to verify module is working.
   */
  public function testRoute() {
    return [
      '#markup' => '<h1>Custom Job URLs Module is Working!</h1><p>Test your SEO URLs now.</p>',
    ];
  }

  /**
   * Debug method to check route matching.
   */
  public function debugRoute($job_category, $location = NULL) {
    return [
      '#markup' => '<h1>Debug Route Works!</h1>
        <p>Job Category: ' . htmlspecialchars($job_category) . '</p>
        <p>Location: ' . htmlspecialchars($location ?: 'Not provided') . '</p>
        <p>This means your route is matching correctly!</p>',
    ];
  }

  /**
   * Simple debug method.
   */
  public function simpleDebug($param) {
    return [
      '#markup' => '<h1>Simple Debug Works!</h1>
        <p>Parameter: ' . htmlspecialchars($param) . '</p>',
    ];
  }

  /**
   * Exact debug method.
   */
  public function exactDebug() {
    return [
      '#markup' => '<h1>Exact Debug Route Works!</h1>
        <p>The exact route /test-information-technolgy-jobs-in-islamabad is working!</p>',
    ];
  }

  /**
   * Pattern debug method.
   */
  public function patternDebug($job_category, $location) {
    return [
      '#markup' => '<h1>Pattern Debug Works!</h1>
        <p>Job Category: ' . htmlspecialchars($job_category) . '</p>
        <p>Location: ' . htmlspecialchars($location) . '</p>
        <p>Pattern matching is working correctly!</p>',
    ];
  }
}