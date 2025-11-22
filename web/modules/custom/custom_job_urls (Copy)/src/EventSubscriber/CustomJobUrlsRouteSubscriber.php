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
    // Test route with more specific path to avoid conflicts
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
    
    // Simple test route (this works!)
    $simple_test = new Route(
      '/simple-test-{param}',
      [
        '_controller' => '\Drupal\custom_job_urls\Controller\JobController::simpleTest',
        '_title' => 'Simple Test',
      ],
      [
        '_permission' => 'access content',
        'param' => '.+',
      ]
    );
    $collection->add('custom_job_urls.simple_test', $simple_test);
    
    // Very specific route without location - add priority weight
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
    
    // Very specific route with location
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