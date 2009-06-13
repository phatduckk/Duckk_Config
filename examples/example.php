<?php
/**
 * This is a quick example of how to use the Duckk_Config class.
 * All you need to do is instanciate the class by passing it a path to
 * a specific ini file. Any names "less specific" than the one provided
 * will be parsed and any intersecting values will be overwritten.
 *
 * This example loads up the config for 
 * <this_packages_path>/examples/configFiles/arin.dev.digg.internal.ini
 * That mean the class will: 
 *   1) load digg.internal.ini
 *   2) override that with stuff from dev.digg.internal.ini
 *   3) and finally override the result of #2 with arin.dev.digg.internal.ini
 */

// setup include_path other overhead to run the example
$dir = dirname(__FILE__);
error_reporting(E_ALL);
set_include_path(realpath("{$dir}/../Duckk_Config"));
require_once 'Duckk/Config.php';

// figure out what the absolute path to examples/configFiles/ini/arin.dev.digg.internal.ini is on your machine
$pathParts      = array($dir, 'configFiles', 'ini', 'arin.dev.digg.internal.ini');
$configFilePath = implode(DIRECTORY_SEPARATOR, $pathParts);

// load the config & print it out
$config = Duckk_Config::getInstance($configFilePath);
print_r($config);

?>