<?php

namespace Drupal\image_cleanup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class CleanupController extends ControllerBase {

  public function run() {
    // Path to CSV file in Drupal public files
    $csv_file = 'public://images.csv';
    $image_dir = 'public://news-items/news';

    $file_system = \Drupal::service('file_system');
    $csv_path = $file_system->realpath($csv_file);
    $image_dir_path = $file_system->realpath($image_dir);

    // Check if CSV file exists
    if (!file_exists($csv_path)) {
      return new Response('CSV file not found at ' . $csv_path);
    }

    // Load and decode image names from CSV
    $csv_images = array_map(function ($line) {
      return urldecode(trim($line));
    }, file($csv_path));
    $csv_images = array_filter($csv_images); // remove empty lines

    $deleted = [];

    // Scan image directory
    if (is_dir($image_dir_path)) {
      $files = scandir($image_dir_path);

      foreach ($files as $file) {
        if (in_array($file, ['.', '..'])) {
          continue;
        }

        $filepath = $image_dir_path . '/' . $file;

        if (is_file($filepath) && !in_array($file, $csv_images)) {
          unlink($filepath);
          $deleted[] = $file;
        }
      }
    }

    if (empty($deleted)) {
      return new Response('No unmatched images found to delete.');
    }

    return new Response('Deleted files:<br>' . implode('<br>', $deleted));
  }

}
