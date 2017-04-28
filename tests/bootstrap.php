<?php

require __DIR__ . '/../vendor/autoload.php';

define('FIXTURE_PATH', __DIR__ . '/fixture');

spl_autoload_register(function ($class) {
    if (strpos($class, 'Mock') !== 0) {
        return;
    }
    $class = str_replace('Mock', 'mock', $class);
    $class = str_replace('\\', '/', $class);

    $path = __DIR__ . "/$class.php";
    if (file_exists($path)) {
        require $path;
    }
});
