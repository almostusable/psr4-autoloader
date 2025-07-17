<?php

require_once __DIR__ . '/src/Psr4Autoloader.php';

use AlmostUsable\Psr4Autoloader\Psr4Autoloader;

$autoloader = new Psr4Autoloader();

$autoloader->loadMappingsFromComposer();
$autoloader->register();

return $autoloader;
