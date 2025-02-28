<?php
return [
    'remote_storage' => [
        'driver' => 'file'
    ],
    'backend' => [
        'frontName' => 'admin'
    ],
    'config' => [
        'async' => 0
    ],
    'crypt' => [
        'key' => 'base64zYtrKh77P8K1llFhahY1V0Bm7jxtDCDbiaKet2TRDR8='
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'magento.cf9xmat0mrs2.us-east-1.rds.amazonaws.com',
                'dbname' => 'magentodb',
                'username' => 'magento',
                'password' => 'Root12345',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'default',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '189_'
            ],
            'page_cache' => [
                'id_prefix' => '189_'
            ]
        ],
        'allow_parallel_generation' => false
    ],
    'lock' => [
        'provider' => 'db'
    ],
    'directories' => [
        'document_root_is_pub' => true
    ]
];