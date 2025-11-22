<?php
/**
 * @file
 * Contains \Drupal\MenuType\Plugin\Field\FieldType\MenuTypeDefaultWidget.
 */
namespace Drupal\landing_page_menu\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'MenuTypeDefaultWidget' widget.
 *
 * @FieldWidget(
 *   id = "MenuTypeDefaultWidget",
 *   label = @Translation("MenuTypeDefaultWidget"),
 *   field_types = {
 *     "MenuType"
 *   }
 * )
 */
class MenuTypeDefaultWidget extends WidgetBase {

  /**
   * Define the form for the field type.
   * 
   * Inside this method we can define the form used to edit the field type.
   * 
   * Here there is a list of allowed element types: https://goo.gl/XVd4tA
   */
  public function formElement(FieldItemListInterface $items,$delta, Array $element, Array &$form, FormStateInterface $formState) {

    $element['text'] = [
      '#title' => $this->t('Text'),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->text) ? $items[$delta]->text : null,
    ];

    $element['link'] = [
      '#type' => 'textfield',
      '#title' => t('Link'),
      '#default_value' => isset($items[$delta]->link) ? $items[$delta]->link : null, 
    ];

    $element['color'] = [
      '#type' => 'textfield',
      '#title' => t('Color'),
      '#default_value' => isset($items[$delta]->color) ? $items[$delta]->color : null,
    ];

    $element['textcolor'] = [
      '#type' => 'textfield',
      '#title' => t('Text color'),
      '#default_value' => isset($items[$delta]->textcolor) ? $items[$delta]->textcolor : null,
    ];

    return $element;
  }

} // class