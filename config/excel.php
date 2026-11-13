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
         * Directory for the temporary workbook. The directory must exist.
         */
        'temporary_path' => sys_get_temp_dir(),

        /**
         * Date fragment appended to the stored and downloaded file name.
         */
        'date_format' => 'Ymd_Hi',

        /**
         * Written when a cell has no value.
         */
        'empty_value' => '',

        /**
         * Display format for columns whose name ends in "_date".
         */
        'formats' => [
            'date' => 'j M Y',
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
