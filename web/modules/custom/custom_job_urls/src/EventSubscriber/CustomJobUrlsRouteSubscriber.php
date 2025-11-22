<?php

namespace Drupal\custom_job_urls\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Listens to the dynamic route events.
 */
class CustomJobUrlsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Simple test route that we know works
    $test_route = new Route(
      '/custom-job-test',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::testRoute',
        '_title' => 'Test Route',
      ],
      [
        '_permission' => 'access content',
      ]
    );
    $collection->add('custom_job_urls.test_route', $test_route);

    // Very simple debug route to test basic pattern matching
    $simple_debug = new Route(
      '/simple-test-{param}',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::simpleDebug',
        '_title' => 'Simple Debug',
      ],
      [
        '_permission' => 'access content',
        'param' => '.+',
      ]
    );
    $collection->add('custom_job_urls.simple_debug', $simple_debug);

    // Test with exact pattern first
    $exact_debug = new Route(
      '/test-information-technolgy-jobs-in-islamabad',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::exactDebug',
        '_title' => 'Exact Debug',
      ],
      [
        '_permission' => 'access content',
      ]
    );
    $collection->add('custom_job_urls.exact_debug', $exact_debug);

    // Now try the pattern version with very loose constraints
    $pattern_debug = new Route(
      '/pattern-{job_category}-jobs-in-{location}',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::patternDebug',
        '_title' => 'Pattern Debug',
      ],
      [
        '_permission' => 'access content',
        'job_category' => '.+',
        'location' => '.+',
      ]
    );
    $collection->add('custom_job_urls.pattern_debug', $pattern_debug);

    // REMOVE all other routes temporarily to avoid conflicts
    // We'll add them back once we get the basic pattern working

    // Legacy routes (keeping these for now since they work)
    $route2 = new Route(
      '/jobsearch/{job_category}',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::jobListing',
        '_title' => 'Jobs',
        'location' => NULL,
      ],
      [
        '_permission' => 'access content',
        'job_category' => '.+',
      ]
    );
    $collection->add('custom_job_urls.job_listing_no_location', $route2);

    $route = new Route(
      '/jobsearch/{job_category}/in/{location}',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::jobListing',
        '_title' => 'Jobs',
      ],
      [
        '_permission' => 'access content',
        'job_category' => '.+',
        'location' => '.+',
      ]
    );
    $collection->add('custom_job_urls.job_listing', $route);
  }
}