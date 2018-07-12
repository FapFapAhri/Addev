<?php
$configuration->loadFromExtension('doctrine', array(
    'dbal' => array(
        'charset' => 'utf8mb4',
        'default_table_options' => array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        )
    ),
));