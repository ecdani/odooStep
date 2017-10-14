<?php
/**
 * Callback de las peticiones ajax de la página de config.
 */

require_once ("CpApp.class.php");

/**
 * Manejador AJAX
 */
if (isset($_POST["option"]) && $_POST["option"] == "SAVE") {
	$o = new cpApp();
	echo $o->saveConf($_POST);
}

?>