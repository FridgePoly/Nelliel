<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

spl_autoload_register(function ($class)
{
    $prefix = 'Nelliel\\';
    $base_directory = __DIR__ . '/';
    nel_autoload_core($class, $prefix, $base_directory);
});

spl_autoload_register(function ($class)
{
    $prefix = 'Nelliel\\';
    $base_directory = __DIR__ . '/classes/';
    nel_autoload_core($class, $prefix, $base_directory);
});

function nel_autoload_core($class, $prefix, $base_directory)
{
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0)
    {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_directory . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file))
    {
        require $file;
    }
}

require_once LIBRARY_PATH . 'phpDOMExtend/autoload.php';
require_once LIBRARY_PATH . 'NellielTemplates/autoload.php';
require_once LIBRARY_PATH . 'SmallPHPGettext/autoload.php';
