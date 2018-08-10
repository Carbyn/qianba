<?php
define('APPLICATION_PATH', dirname(__FILE__).'/..');
require_once(APPLICATION_PATH.'/vendor/autoload.php');
$app = new Yaf\Application(APPLICATION_PATH.'/conf/application.ini');
