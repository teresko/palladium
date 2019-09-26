<?php

require __DIR__ . '/../vendor/autoload.php';

copy(__DIR__ . '/fixture/integration.sqlite', sys_get_temp_dir() . '/db.sqlite');
chmod(sys_get_temp_dir() . '/db.sqlite', 0777);

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
