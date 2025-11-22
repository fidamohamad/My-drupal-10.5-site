<?php
/**
 * @file
 * Contains \Drupal\MenuType\Plugin\Field\FieldType\MenuType.
 */

namespace Drupal\landing_page_menu\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface as StorageDefinition;

/**
 * Plugin implementation of the 'MenuType' field type.
 *
 * @FieldType(
 *   id = "MenuType",
 *   label = @Translation("Landing Page Menu"),
 *   description = @Translation("Landing Page Menu."),
 *   category = @Translation("Custom"),
 *   default_widget = "MenuTypeDefaultWidget",
 *   default_formatter = "MenuTypeDefaultFormatter"
 * )
 */
class MenuType extends FieldItemBase {

  /**
   * Field type properties definition.
   * 
   * Inside this method we defines all the fields (properties) that our 
   * custom field type will have.
   * 
   * Here there is a list of allowed property types: https://goo.gl/sIBBgO
   */
  public static function propertyDefinitions(StorageDefinition $storage) {

    $properties = [];

    $properties['text'] = DataDefinition::create('string')
      ->setLabel(t('Text'));

    $properties['link'] = DataDefinition::create('string')
      ->setLabel(t('Link'));

    $properties['color'] = DataDefinition::create('string')
      ->setLabel(t('Color'));

    $properties['textcolor'] = DataDefinition::create('string')
      ->setLabel(t('Text color'));

    return $properties;
  }

  /**
   * Field type schema definition.
   * 
   * Inside this method we defines the database schema used to store data for 
   * our field type.
   * 
   * Here there is a list of allowed column types: https://goo.gl/YY3G7s
   */
  public static function schema(StorageDefinition $storage) {

    $columns = [];
    $columns['text'] = [
      'type' => 'char',
      'length' => 255,
    ];
     $columns['link'] = [
      'type' => 'char',
      'length' => 255,
    ];

     $columns['color'] = [
      'type' => 'char',
      'length' => 255,
    ];

     $columns['textcolor'] = [
      'type' => 'char',
      'length' => 255,
    ];

    return [
      'columns' => $columns,
      'indexes' => [],
    ];
  }

  /**
   * Define when the field type is empty. 
   * 
   * This method is important and used internally by Drupal. Take a moment
   * to define when the field fype must be considered empty.
   */
  public function isEmpty() {

    $link = $this->get('link')->getValue();
    $label = $this->get('text')->getValue();
    $color = $this->get('color')->getValue();
    $textcolor = $this->get('textcolor')->getValue();
    return empty($link) && empty($label) && empty($color) && empty($textcolor);

  }
  
} // class