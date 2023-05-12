<?php

$paths = require __DIR__ . '/config/paths.php';

if (file_exists($paths['path.sys'] . '/src/bundles/WebResourceBundle/Bootstrap/bootstrap.php')) {
    require $paths['path.sys'] . '/src/bundles/WebResourceBundle/Bootstrap/bootstrap.php';
}
