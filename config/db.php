<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'pgsql:host=localhost;dbname=nispd',
    'username' => '',
    'password' => '',
    'charset' => 'utf8',
    'tablePrefix' => '',
    'enableSchemaCache' => true,
    'schemaMap' => [
        'pgsql' => [
            'class' => \yii\db\pgsql\Schema::class,
            'columnSchemaClass' => [
                'class' => \yii\db\pgsql\ColumnSchema::class,
                'disableJsonSupport' => true,
                'disableArraySupport' => true,
                'deserializeArrayColumnToArrayExpression' => false,
            ],
        ],
    ],

];
