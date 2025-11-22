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
   */
  public function jobListing($job_category, $location = NULL) {
    // Convert URL segments to readable format
    $job_name = str_replace('-', ' ', $job_category);
    $job_name = ucwords(strtolower($job_name));
    
    // Prepare exposed filters input
    $exposed_input = [];
    
    // Get job term ID and format the filter value
    $job_term_id = $this->getTermIdByName($job_name, 'category');
    if (!$job_term_id) {
      $job_term_id = $this->findTermByPartialMatch($job_name, 'category');
    }
    
    if ($job_term_id) {
      // Format exactly like your view expects: "Information technolgy (4)"
      $exposed_input['job'] = $job_name . ' (' . $job_term_id . ')';
    }
    
    // Handle location if provided
    if ($location) {
      $location_name = str_replace('-', ' ', $location);
      $location_name = ucwords(strtolower($location_name));
      
      // Replace 'location' with your actual location vocabulary machine name
      $location_term_id = $this->getTermIdByName($location_name, 'location'); // REPLACE 'location' with your vocabulary name
      
      if ($location_term_id) {
        // Format: "Islamabad (3)"
        $exposed_input['location'] = $location_name . ' (' . $location_term_id . ')';
      }
    }
    
    // Load and render the view with the correct method
    $view_storage = \Drupal::entityTypeManager()->getStorage('view');
    $view_entity = $view_storage->load('jobs');
    
    if (!$view_entity) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('View not found');
    }
    
    $view = \Drupal::service('views.executable')->get($view_entity);
    
    // Set the display (check your view for the correct display ID)
    $view->setDisplay('page_1'); // You may need to change this to 'default' or another display ID
    
    // Apply the exposed filters
    $view->setExposedInput($exposed_input);
    
    // Execute the view
    $view->preExecute();
    $view->execute();
    
    // Build the render array
    $build = [];
    
    // Add the view output
    $build['view'] = $view->buildRenderable('page_1', [], FALSE); // Match the display ID above
    
    // Set the page title dynamically
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
    
    // Add cache tags and contexts for proper caching
    $build['#cache']['tags'][] = 'taxonomy_term_list:category';
    if (isset($location_term_id)) {
      $build['#cache']['tags'][] = 'taxonomy_term_list:location'; // Update with your location vocabulary
    }
    $build['#cache']['contexts'][] = 'url.path';
    
    return $build;
  }
  
  /**
   * Get term ID by exact name match.
   */
  private function getTermIdByName($term_name, $vocabulary) {
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $term_name,
        'vid' => $vocabulary,
      ]);
    
    if (!empty($terms)) {
      $term = reset($terms);
      return $term->id();
    }
    
    return NULL;
  }
  
  /**
   * Find term by partial match (in case of typos like "technolgy").
   */
  private function findTermByPartialMatch($search_name, $vocabulary) {
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vocabulary)
      ->accessCheck(FALSE);
    
    $tids = $query->execute();
    
    if (empty($tids)) {
      return NULL;
    }
    
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
    
    // Try to find the best match
    $search_lower = strtolower($search_name);
    $best_match = NULL;
    $best_score = 0;
    
    foreach ($terms as $term) {
      $term_name_lower = strtolower($term->getName());
      
      // Calculate similarity
      $similarity = 0;
      similar_text($search_lower, $term_name_lower, $similarity);
      
      if ($similarity > $best_score && $similarity > 70) { // 70% similarity threshold
        $best_score = $similarity;
        $best_match = $term->id();
      }
    }
    
    return $best_match;
  }
  
  /**
   * Simple test method to check if routing works.
   */
  public function simpleTest($param) {
    return [
      '#markup' => '<h1>Simple Test Works!</h1><p>Parameter received: ' . $param . '</p>',
    ];
  }
  
  /**
   * Test route to verify module is working.
   */
  public function testRoute() {
    return [
      '#markup' => '<h1>Custom Job URLs Module is Working!</h1><p>Test your SEO URLs now.</p>',
    ];
  }
}