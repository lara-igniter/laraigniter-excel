<?php

$config = [
    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Values used when an export call does not set them. Publish this file
    | with "php artisan vendor:publish --tag=excel-config". A published
    | config/excel.php replaces these values. setExtension() still wins
    | over exports.extension for that call.
    |
    */

    'exports' => [

        /**
         * File extension when setExtension() was not called.
         *
         * Supported: "xlsx", "xls".
         */
        'extension' => 'xlsx',

        /**
         * Date fragment appended to the stored and downloaded file name.
         */
        'date_format' => 'Ymd_Hi',

        /**
         * Written when a cell has no value.
         */
        'empty_value' => '',

        /**
         * Date display formats, keyed by the column-name suffix.
         * Add "_at" with its own format for created_at and updated_at.
         */
        'formats' => [
            'dates' => [
                '_date' => 'j M Y',
            ],
        ],

        /**
         * Workbook properties. An empty string leaves the spreadsheet default.
         * An export that implements WithProperties replaces only the keys it returns.
         */
        'properties' => [
            'creator' => '',
            'title' => '',
            'company' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary files
    |--------------------------------------------------------------------------
    |
    | The writer stores the workbook here before it is downloaded. The path
    | stays inside the project. local_permissions sets the directory mode
    | when the directory is created and the file mode after the workbook is
    | saved. Leave a flag out to keep the filesystem default.
    |
    */
    'temporary_files' => [
        /*
        |--------------------------------------------------------------------------
        | Local Temporary Path
        |--------------------------------------------------------------------------
        |
        | The writer saves the workbook in this directory before the file is
        | downloaded. The directory is created when it does not exist.
        |
        */
        'local_path' => storage_path('framework/cache/laraigniter-excel'),

        /*
        |--------------------------------------------------------------------------
        | Local Temporary Path Permissions
        |--------------------------------------------------------------------------
        |
        | dir is applied when the directory is created. file is applied to the
        | workbook after it is saved. A missing flag keeps the filesystem default.
        |
        */
        'local_permissions' => [
            // 'dir' => 0755,
            // 'file' => 0644,
        ],
    ],

    'activity' => [

        /**
         * Activity log name passed to activity().
         */
        'log' => 'exports',

        /**
         * Event name stored with the download entry.
         */
        'event' => 'export.downloaded',
    ],

    'filters' => [

        /**
         * Query keys that contain one of these terms are left out of the log.
         */
        'sensitive' => [
            'address',
            'comment',
            'email',
            'identity',
            'lastname',
            'mobile',
            'name',
            'note',
            'number',
            'password',
            'phone',
            'search',
            'ssn',
            'tax',
            'token',
        ],

        /**
         * Longest string filter value written to the log.
         */
        'max_length' => 100,

        /**
         * A key that contains "date" and ends with one of these suffixes
         * is stored in the date range.
         */
        'date_suffixes' => [
            '_from',
            '_to',
        ],
    ],
];
