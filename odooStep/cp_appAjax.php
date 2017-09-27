<?php
/**
 * Callback de las peticiones ajax de la página de config.
 */
require_once ("classes/model/OdooStepConf.php");

require_once ("classes/model/OdooStepConfPeer.php");

Propel::init(PATH_CORE . "config/databases.php");

try {
	/*
	"respuesta": {
	"option": "SAVE",
	"txtUrl": "http:\/\/localhost:8069",
	"txtDb": "da",
	"txtUsuario": "da",
	"txtPassword": "a"
	}

	$GLOBALS
	$_SERVER
	$_REQUEST
	$_POST
	$_GET
	$_FILES
	$_ENV
	$_COOKIE
	$_SESSION
	*/
	$option = $_POST["option"];

	// $APP_UID = $_SESSION['APPLICATION'];

	switch ($option) {
	case "SAVE":
		try {
            $osconf = OdooStepConfPeer::retrieveByPK(1);
			//$osconf = new OdooStepConf();
			//$osconf->setId(1);
            if ( is_null ( $osconf ) ) {
                $osconf = new OdooStepConf();

            } 
            			$osconf->setUrl($_POST["txtUrl"]);
			$osconf->setDb($_POST["txtDb"]);
			$osconf->setUsername($_POST["txtUsuario"]);
			$osconf->setPassword($_POST["txtPassword"]);
			$osconf->save();
		}

		catch(Exception $e) {
			throw $e;
		}

		echo G::json_encode(array(
			"success" => true,
			"salvando" => true,
			"respuesta" => $_POST
		));
		break;
		/**
		 * Create "Process Variables" records
		 *
		 * @param array $arrayData Data to create
		 *
		 * return void
		 */
		/*
		try {
		foreach ($_POST as $value) {
		$processVariables = new ProcessVariables();
		$record = $value;
		if ($processVariables->Exists($record["VAR_UID"])) {
		$result = $processVariables->remove($record["VAR_UID"]);
		}

		$result = $processVariables->create($record);
		}
		} catch (Exception $e) {
		throw $e;
		}*/
	case "LOAD":

		// with Peer constants and static methods

		$osconf = "holi";
		try {
			try {

				// $con = Propel::getConnection('workflow');

				Propel::init(PATH_CORE . "config/databases.php");
				$con = Propel::getConnection(OdooStepConfPeer::DATABASE_NAME);

				// $con->begin();

				/*$sql = 'SELECT * FROM ODOOSTEP_CONFIG';
				$stmt = $con->createStatement();
				$rs = $stmt->executeQuery($sql, ResultSet::FETCHMODE_NUM);

				// sleep(3);

				$salida = OdooStepConfPeer::populateObjects($rs);

				// $con->commit();*/

				$OdooStepConf = OdooStepConfPeer::retrieveByPK(1);
                if (! is_null ( $OdooStepConf ) ) {
                //$OdooStepConf = new OdooStepConf();
                    $array = $OdooStepConf->toArray();
                } 
				

				// echo $rs;
				// return $osconfs;

			}

			catch(PropelException $e) {
				$con->rollback();
				throw $e;
			}

			echo G::json_encode(array(
				"loading" => true,
				"respuesta" => $array
			));
		}

		catch(Error $err) {
			echo "catched: ", $err->getMessage() , PHP_EOL;
		}

		break;

	case "LST":
		$pageSize = $_POST["pageSize"];
		$limit = isset($_POST["limit"]) ? $_POST["limit"] : $pageSize;
		$start = isset($_POST["start"]) ? $_POST["start"] : 0;
		list($userNum, $user) = userGet($limit, $start);

		// echo "{success: " . true . ", resultTotal: " . count($user) . ", resultRoot: " . G::json_encode($user) . "}";

		echo G::json_encode(array(
			"success" => true,
			"respuesta" => $_POST
		));
		break;
	}
}

catch(Exception $e) {
	echo G::json_encode(array(
		"Error" => true,
		"respuesta" => $e->getMessage()
	));
}

catch(Error $err) {
	echo "catched: ", $err->getMessage() , PHP_EOL;
}

?>