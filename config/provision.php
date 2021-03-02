<?php

return [
    // How to connect to the provision database
    'database' => [
        // The connection name (should exist in config/database)
        'connection' => 'default',

        // The provisionning table
        'table' => 'provisions',
    ],

    // Folder where the provision files are stored
    'folder' => 'database/provisions',
];
