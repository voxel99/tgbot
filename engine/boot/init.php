<?php
if (!isset($JAM_ROOT_DIR)) {
    $JAM_ROOT_DIR = dirname(dirname(dirname(__FILE__)));
}
require 'Jam.php';
$GLOBALS['jam'] = new Jam($JAM_ROOT_DIR);
require $JAM_ROOT_DIR.'/vendor/autoload.php';
require 'functions.php';

// Соединения для моделей jam/dbsimple-models
\Jam\Models\Model::initDbSimple([
    \Jam\Models\Model::DB_MASTER => db(\Jam\Models\Model::DB_MASTER),
]);
