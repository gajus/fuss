<?php
require __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/config.php')) {
    throw new \Exception('Cannot run tests without test app credentials. Rename config.php.dist to config.php.');
}

require __DIR__ . '/config.php';