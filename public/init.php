<?php

define('ROOT', dirname(dirname(__FILE__)));

require_once  ROOT . "/engine/boot/init.php";
require_once  ROOT . "/app/functions.php";

define('TOKENINFO_PATH', env('TOKENINFO_PATH', ROOT . '/docusign.json'));
