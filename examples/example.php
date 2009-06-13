<?php

// setup
$dir = dirname(__FILE__);
set_include_path(realpath("$dir/../Duckk_Config"));
require_once 'Duckk/Config.php';

// create absolute path to arin.dev.digg.internal.ini (its in this project's examples/configFiles/ini folder)
$pathParts      = array($dir, 'configFiles', 'ini', 'arin.dev.digg.internal.ini');
$configFilePath = implode(DIRECTORY_SEPARATOR, $pathParts);

// load the config & print it out
$config = Duckk_Config::getInstance($configFilePath);
print_r($config);

?>