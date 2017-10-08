
<?php

function revert(&$v, $k) {
	$v = "$k:$v\n";
}

function stepGet($r, $i, $textFilter) {
	$c = new Criteria();
	$c->addDescendingOrderByColumn('ID');
	$c->setLimit($r);
	$c->setOffset($i);
	if ($textFilter) {
		$c->add(OdooStepStepPeer::NOMBRE, '%' . $textFilter . '%', Criteria::LIKE);
	}

	$steps = OdooStepStepPeer::doSelect($c);
	$return = array();
	foreach($steps as $k => $v) {
		$p = unserialize($v->getParameters());
		$p = implode(",", $p);
		$kwp = unserialize($v->getKwParameters());

		// print_r($kwp);
		// print_r("----");

		array_walk($kwp, 'revert');
		$kwp = implode("\n", array_values($kwp));

		// print_r($kwp);

		$return[] = array(
			"ID" => $v->getId() ,
			"PRO_UID" => $v->getProUid() ,
			"NOMBRE" => $v->getNombre() ,
			"MODEL" => $v->getModel() ,
			"METHOD" => $v->getMethod() ,
			"PARAMETERS" => $p,
			"KW_PARAMETERS" => $kwp,
			"OUTPUT" => $v->getOutput()
		);
	}

	return (array(
		count($return) ,
		array_slice($return, $i, $r)
	));
}

function saveStep($stepid) {
	$ostep = OdooStepStepPeer::retrieveByPK($stepid);
	if (!(is_object($ostep) && get_class($ostep) == 'OdooStepStep')) {
		$ostep = new OdooStepStep();
	}

	$ostep->setNombre($_POST["newNombre"]);
	$ostep->setProUid($_POST["newProceso"]);
	$ostep->setModel($_POST["newModelo"]);
	$ostep->setMethod($_POST["newMetodo"]);
	$ostep->setOutput($_POST["newSalida"]);
	$parametros = preg_split("/[\s,]+/", $_POST["newParametros"]); // Separación v,v,v,...
	preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $_POST["newParametrosKW"], $p); // Separación k:v,v,v INTRO k:v,.... que no sean iguales la k.
	$kwparams = array_combine($p[1], $p[2]);
	$ostep->setParameters(serialize($parametros));
	$ostep->setKwParameters(serialize($kwparams));
	$ostep->save();
	return $ostep;
}

try {
	switch ($_POST["option"]) {
	case "LST":
		$pageSize = $_POST["pageSize"];
		$limit = isset($_POST["limit"]) ? $_POST["limit"] : $pageSize;
		$start = isset($_POST["start"]) ? $_POST["start"] : 0;
		$textFilter = isset($_POST["textFilter"]) ? $_POST["textFilter"] : "";
		list($userNum, $user) = stepGet($limit, $start, $textFilter);

		// echo "{success: " . true . ", resultTotal: " . count($user) . ", resultRoot: " . G::json_encode($user) . "}";

		echo G::json_encode(array(
			"success" => true,
			"resultTotal" => $userNum,
			"resultRoot" => $user
		));
		break;

	case "UPDATESTEP":
		saveStep($_POST["id"]);
		break;
	case "DELETESTEP":
		$ostep = OdooStepStepPeer::retrieveByPK($_POST["id"]);
		if (is_object($ostep) && get_class($ostep) == 'OdooStepStep') {
			$ostep->delete();
		}
	break;

	case "NEWSTEP":
		try {
			$c = new Criteria();
			$c->addDescendingOrderByColumn('ID');
			$lastStep = OdooStepStepPeer::doSelectOne($c);
			if (!is_null($lastStep)) {
				$id = $lastStep->getId();
				$stepid = $lastStep->getStepId();
				$contador = substr($stepid, -4);
				$int = (int)$contador;
				$int++;
				$stri = (string)$int;
				$strlen = strlen($stri);
				$stepid = substr_replace($stepid, $stri, -$strlen);
			} else {
				$stepid = "4553885635943a689c55440011040000";
			}

			$ostep = saveStep($stepid);

			$oPluginRegistry = PMPluginRegistry::getSingleton();
			$oPluginRegistry->registerStep("odooStep", $ostep->getStepId() , "stepodooStepApplication", $ostep->getNombre());
			$oPluginRegistry->save();
		} catch(Exception $e) {
			throw $e;
			echo G::json_encode(array(
				"excepcion" => $e
			));
		}

		echo G::json_encode(array(
			"success" => true,
			"salvando" => true,
			"respuesta" => print_r($_SESSION)
		));
		break;
	}
} catch(Exception $e) {
	echo null;
}

?>
