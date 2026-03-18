<?php

// PHP built-in server: serve static files directly
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

session_start();

// Load helpers & config
require_once __DIR__ . '/../config/app.php';

// Load .env
loadEnv(__DIR__ . '/../.env');

// Error reporting based on debug mode
if (env('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Autoload core classes
spl_autoload_register(function (string $class) {
    $paths = [
        __DIR__ . '/../core/',
        __DIR__ . '/../app/Controllers/',
        __DIR__ . '/../app/Models/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load routes
require_once __DIR__ . '/../routes.php';

// Dispatch the request
Router::dispatch();

// Clear old input after request
unset($_SESSION['_old_input']);
