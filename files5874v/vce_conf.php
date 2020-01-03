<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* variables */
define ('DB_PREFIX', 'oc_');
define ('LANGUAGE_ID', 1);
define ('STORE_ID', 0);

/* connect to DataBase */
$host = 'localhost';
$user = '****';
$pass = '****';
$dataBase = 'vce';
$db = new mysqli($host, $user, $pass, $dataBase);

if ($db->connect_errno > 0) {
	die("Unable to connect to database [" . $db->connect_error . "]");
}

?>