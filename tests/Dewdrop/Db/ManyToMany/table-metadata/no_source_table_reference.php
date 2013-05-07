<?php

return array(
    'titles' => array(
        'singular' => 'Dewdrop Test Fruits Eaten By Animal',
        'plural'   => 'Dewdrop Test Fruits Eaten By Animals'
    ),
    'columns' => array (
  'animal_id' =>
  array (
    'SCHEMA_NAME' => NULL,
    'TABLE_NAME' => 'dewdrop_test_fruits_eaten_by_animals',
    'COLUMN_NAME' => 'animal_id',
    'COLUMN_POSITION' => 1,
    'DATA_TYPE' => 'int',
    'DEFAULT' => NULL,
    'NULLABLE' => false,
    'LENGTH' => NULL,
    'SCALE' => NULL,
    'PRECISION' => NULL,
    'UNSIGNED' => NULL,
    'PRIMARY' => true,
    'PRIMARY_POSITION' => 1,
    'IDENTITY' => false,
  ),
  'fruit_id' =>
  array (
    'SCHEMA_NAME' => NULL,
    'TABLE_NAME' => 'dewdrop_test_fruits_eaten_by_animals',
    'COLUMN_NAME' => 'fruit_id',
    'COLUMN_POSITION' => 2,
    'DATA_TYPE' => 'int',
    'DEFAULT' => NULL,
    'NULLABLE' => false,
    'LENGTH' => NULL,
    'SCALE' => NULL,
    'PRECISION' => NULL,
    'UNSIGNED' => NULL,
    'PRIMARY' => true,
    'PRIMARY_POSITION' => 2,
    'IDENTITY' => false,
  ),
),
    'references' => array (
  'fruit_id' =>
  array (
    'table' => 'dewdrop_test_fruits',
    'column' => 'dewdrop_test_fruit_id',
  ),
)
);
