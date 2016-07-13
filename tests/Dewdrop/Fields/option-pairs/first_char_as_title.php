<?php

return array(
    'titles' => array(
        'singular' => 'Dewdrop Test Fruit',
        'plural'   => 'Dewdrop Test Fruits'
    ),
    'columns' => array (
  'dewdrop_test_fruit_id' =>
  array (
    'SCHEMA_NAME' => NULL,
    'TABLE_NAME' => 'dewdrop_test_fruits',
    'COLUMN_NAME' => 'dewdrop_test_fruit_id',
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
    'IDENTITY' => true,
    'GENERIC_TYPE' => 'integer'
  ),
  'just_a_char' =>
  array (
    'SCHEMA_NAME' => NULL,
    'TABLE_NAME' => 'dewdrop_test_fruits',
    'COLUMN_NAME' => 'just_a_char',
    'COLUMN_POSITION' => 2,
    'DATA_TYPE' => 'varchar',
    'DEFAULT' => NULL,
    'NULLABLE' => false,
    'LENGTH' => '128',
    'SCALE' => NULL,
    'PRECISION' => NULL,
    'UNSIGNED' => NULL,
    'PRIMARY' => false,
    'PRIMARY_POSITION' => NULL,
    'IDENTITY' => false,
    'GENERIC_TYPE' => 'text'
  ),
  'is_delicious' =>
  array (
    'SCHEMA_NAME' => NULL,
    'TABLE_NAME' => 'dewdrop_test_fruits',
    'COLUMN_NAME' => 'is_delicious',
    'COLUMN_POSITION' => 3,
    'DATA_TYPE' => 'tinyint',
    'DEFAULT' => '1',
    'NULLABLE' => false,
    'LENGTH' => NULL,
    'SCALE' => NULL,
    'PRECISION' => NULL,
    'UNSIGNED' => NULL,
    'PRIMARY' => false,
    'PRIMARY_POSITION' => NULL,
    'IDENTITY' => false,
  ),
  'level_of_deliciousness' =>
  array (
    'SCHEMA_NAME' => NULL,
    'TABLE_NAME' => 'dewdrop_test_fruits',
    'COLUMN_NAME' => 'level_of_deliciousness',
    'COLUMN_POSITION' => 4,
    'DATA_TYPE' => 'int',
    'DEFAULT' => '0',
    'NULLABLE' => false,
    'LENGTH' => NULL,
    'SCALE' => NULL,
    'PRECISION' => NULL,
    'UNSIGNED' => NULL,
    'PRIMARY' => false,
    'PRIMARY_POSITION' => NULL,
    'IDENTITY' => false,
  ),
),
    'references' => array (
)
);
