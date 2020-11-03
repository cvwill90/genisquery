<?php
// Error reporting 
// set to 0 in production
error_reporting(-1);
ini_set('display_errors', '1');

// Default timezone
date_default_timezone_set('Europe/Paris');

// Settings
$settings = [];

// Genis settings
$settings['db_dump_path'] = getenv("GENIS_DUMP_PATH");
$settings['data_export_path'] = getenv("GENIS_EXPORT_PATH");
$settings['pedig_results_path'] = getenv("GENIS_PEDIG_PATH");
$settings['dsn'] = getenv("PHINX_DSN");

// Path settings
$settings['root_dir'] = dirname(__DIR__);
$settings['public_dir'] = $settings['root_dir'] . '/public';

// Error Handling Middleware settings
$settings['error'] = [

    // Should be set to false in production
    'display_error_details' => true,

    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests we also disable it
    'log_errors' => true,

    // Display error details in error log
    'log_error_details' => true,
];

return $settings;
