<?php

define('APP',dirname(__FILE__).DIRECTORY_SEPARATOR);

require APP. 'config/config.php';
require APP. 'mvc/controller.php';

session_start();

$app = new Controller();
