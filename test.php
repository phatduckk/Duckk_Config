<?php

require_once './Duckk/Config.php';

$parts = array(
    realpath('.'),
    'configFiles',
    'ini',
    'override.arin.bla.dev.ini'
);


$c = Duckk_Config::getInstance(implode(DIRECTORY_SEPARATOR, $parts));
print_r($c);
?>