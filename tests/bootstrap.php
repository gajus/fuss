<?php
require __DIR__ . '/../vendor/autoload.php';

if (isset($_SERVER['TRAVIS'])) {
    define('TEST_APP_ID', $_SERVER['TEST_APP_ID']);
    define('TEST_APP_SECRET', $_SERVER['TEST_APP_SECRET']);
} else {
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new \Exception('Cannot run tests without test app credentials. Rename config.php.dist to config.php.');
    }

    require __DIR__ . '/config.php';
}