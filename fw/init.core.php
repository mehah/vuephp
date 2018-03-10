<?php
$TAGET_CLASS_NAME = $TARGET_NAME = null;
$HAS_METHOD = false;
unset($_SESSION);
if (!isset($_REQUEST ['url'])) {
	$_REQUEST ['url'] = "index";
}

$exURL = explode("/", $_REQUEST ['url'], 3);
$CONTEXT_PATH = str_repeat('../',count($exURL)-1);

if (isset($exURL [1])) {
	$isNumeric = is_numeric($exURL [1]);
	if (!($HAS_METHOD = (strlen($exURL [1]) > 0) && !$isNumeric)) { // Se terminar com /, não executa nada.
		if (!$isNumeric) {
			die();
		} else {
		    $_REQUEST['arg0'] = $exURL [1];
		}
	}
}

include ('fw/VueApp.php');
include ('fw/database/DatabaseConnection.php');
include ('fw/database/DatabaseEntity.php');
include ('fw/database/Entity.php');
include ('database.config.php');

$TARGET_NAME = $exURL [0];
$TAGET_CLASS_NAME = ucfirst($exURL [0]);

spl_autoload_register(function ($class_name) {
	if(strpos($class_name, 'src') === false) {
		$class_name = 'src\\' . $class_name;
	}
	include $class_name . '.php';
});

session_start();

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    include ('fw/content.core.php');
    exit;
}

echo '<!DOCTYPE html><script type="text/javascript" src="'.$CONTEXT_PATH.'fw/vue.min.js"></script>';