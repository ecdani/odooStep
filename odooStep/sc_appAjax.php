<?php

require_once ("ScApp.class.php");

/**
 * Ajax handler of "Odoo Steps Creator" page functionality (CRUD)
 */
$scapp = new scApp();
try {
	switch ($_POST["option"]) {
		case "LST":
			echo G::json_encode($scapp->listSteps($_POST));
			break;
		case "UPDATESTEP":
			echo G::json_encode($scapp->saveStep($_POST));
			break;
		case "DELETESTEP":
			echo G::json_encode($scapp->deleteStep($_POST));
			break;
		case "NEWSTEP":
			echo G::json_encode($scapp->createStep($_POST));
			break;
	}
} catch(Exception $e) {
	echo $e->getMessage();
}

?>
