<?php

namespace Drupal\landing_page_menu\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Plugin implementation of the 'MenuTypeDefaultFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "MenuTypeDefaultFormatter",
 *   label = @Translation("MenuType"),
 *   field_types = {
 *     "MenuType"
 *   }
 * )
 */
class MenuTypeDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $text      = $item->text ?? '';
      $link      = $item->link ?? '';
      $bgcolor   = $item->color ?? '#ffffff';     // Background color from field
      $textcolor = $item->textcolor ?? '#000000'; // Text color from field

      // Build link or plain text with inline text color.
      if (!empty($link)) {
        $url = Url::fromUri($link, [
          'attributes' => [
            'target' => '_blank',
            'style' => 'color: ' . $textcolor . '; text-decoration: none;',
          ],
        ]);
        $link_obj = Link::fromTextAndUrl($text ?: $link, $url)->toRenderable();
      }
      else {
        $link_obj = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $text,
          '#attributes' => [
            'style' => 'color: ' . $textcolor . ';',
          ],
        ];
      }

      // Wrap in a background color container.
      $elements[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'style' => 'background-color: ' . $bgcolor . '; padding: 5px; display: inline-block; margin: 3px;',
        ],
        'content' => $link_obj,
      ];
    }

    return $elements;
  }

}
