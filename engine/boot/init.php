<?php
if (!isset($JAM_ROOT_DIR)) {
    $JAM_ROOT_DIR = dirname(dirname(dirname(__FILE__)));
}
// if (!defined('DBSIMPLE_SKIP')) {
//     define('DBSIMPLE_SKIP', log(0));
// }
require 'Jam.php';
$GLOBALS['jam'] = new Jam($JAM_ROOT_DIR);
require $JAM_ROOT_DIR.'/vendor/autoload.php';
require 'functions.php';
