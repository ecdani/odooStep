<?php

/**
 * Step Configuration App
 * Creation, Edition, Deletion and List of OdooSteps
 */
class scApp {

	/**
	 * Factory method, because static methods can not be emulated.
	 * @param $p Name of class
	 */
	protected function f($p) {
		switch ($p) {
            case "OdooStepConf":
            	return new OdooStepConf();
			case "OdooStepStep":
				return new OdooStepStep();
            break;
        }
    }
	
	/**
	 * Proxy for Peer classes that avoids conflict with
	 * static calls in the SimpleTest testing.
	 * @param $c Class name
	 * @param $f Method name
	 * @param $p parameters
	 */
    protected function proxy($c,$f,$p) {
		return call_user_func_array($c.'::'.$f, array($p)); 
	}

	/**
	 * Get a list of OdooSteps ready to be displayed
 	 * in an ExtJS form.
	 * @param $r Limit of query
	 * @param $i Offset of query
	 * @param $textFilter Search word
	 */
	public function getStep($r, $i, $textFilter) {
		$c = new Criteria();
		$c->addDescendingOrderByColumn('ID');
		$c->setLimit($r);
		$c->setOffset($i);
		if ($textFilter) {
			$c->add(OdooStepStepPeer::NOMBRE, '%' . $textFilter . '%', Criteria::LIKE);
		}

		//$steps = OdooStepStepPeer::doSelect($c);
		$steps = $this->proxy('OdooStepStepPeer','doSelect',$c);
		$return = array();
		foreach($steps as $k => $v) {
			$return[] = array(
				"ID" => $v->getId() ,
				"PRO_UID" => $v->getProUid() ,
				"NOMBRE" => $v->getNombre() ,
				"MODEL" => $v->getModel() ,
				"METHOD" => $v->getMethod() ,
				"PARAMETERS" => $v->getParameters(),//$p,
				"KW_PARAMETERS" => $v->getKwParameters(),//$kwp,
				"OUTPUT" => $v->getOutput()
			);
		}
		return (array(
			count($return) ,
			array_slice($return, $i, $r)
		));
	}

	/**
	 * Save OdooStep
	 * @param $post HTTP POST method result
	 */
	public function saveStep($post) {
		try {
			//$ostep = OdooStepStepPeer::retrieveByPK($stepid);
			$ostep = $this->proxy('OdooStepStepPeer','retrieveByPK',$post["id"]);
			if (!(is_object($ostep) && get_class($ostep) == 'OdooStepStep')) {
				$ostep = $this->f("OdooStepStep");
				$ostep->setStepId($post["id"]);
			}

			$ostep->setNombre($post["newNombre"]);
			$ostep->setProUid($post["newProceso"]);
			$ostep->setModel($post["newModelo"]);
			$ostep->setMethod($post["newMetodo"]);
			$ostep->setOutput($post["newSalida"]);

			$ostep->setParameters($post["newParametros"]);
			$ostep->setKwParameters($post["newParametrosKW"]);
			$ostep->save();
			return array("success" => true, "respuesta" => $ostep);
		} catch (Exception $e) {
			return array("success" => false, "exception" => $e);
		}
	}

	/**
	 * Return the demanded list of OdooSteps since "Odoo Step Creator" page
	 * @param $post HTTP POST method result
	 */
	public function listSteps($post) {
		try {
			$pageSize = $post["pageSize"];
			$limit = isset($post["limit"]) ? $post["limit"] : $pageSize;
			$start = isset($post["start"]) ? $post["start"] : 0;
			$textFilter = isset($post["textFilter"]) ? $post["textFilter"] : "";
			list($userNum, $user) = $this->getStep($limit, $start, $textFilter);
			return array(
				"success" => true,
				"resultTotal" => $userNum,
				"resultRoot" => $user
			);
		} catch (Exception $e){
			return array("success" => false, "exception" => $e);
		}
	}

	/**
	 * Delete a OdooStep
	 * @param $post HTTP POST method result
	 */
	public function deleteStep($post) {
		try {
			//$ostep = OdooStepStepPeer::retrieveByPK($post["id"]);
			$ostep = $this->proxy('OdooStepStepPeer','retrieveByPK',$post["id"]);
			if (is_object($ostep) && get_class($ostep) == 'OdooStepStep') {
				$ostep->delete();
			}
		} catch (PropelException $e) {
			return array( "success" => false, "exception" => $e );
        }
		return array( "success" => true );
	}

	/**
	 * Generate next unique identificator of Step.
	 * The first identificator is one suggested by ProcessMaker documentation... really, its a random one.
	 */
	public function nextStepID() {
		$c = new Criteria();
		$c->addDescendingOrderByColumn('ID');
		//$lastStep = OdooStepStepPeer::doSelectOne($c);
		$lastStep = $this->proxy('OdooStepStepPeer','doSelectOne',$c);
		if (!is_null($lastStep)) {
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
		return $stepid;
	}

	/**
	 * Create a OdooStep
	 * @param $post HTTP POST method result
	 */
	public function createStep($post) {
		try {
			$post["id"] = $this->nextStepID();
			$result = $this->saveStep($post);
			if ($result["success"]) {
				$ostep = $result["respuesta"];
			} else {
				throw $result["exception"];
			}
			#$oPluginRegistry = $this->proxy('PMPluginRegistry','getSingleton');

			$oPluginRegistry = PMPluginRegistry::getSingleton();
			$oPluginRegistry->registerStep("odooStep", $ostep->getStepId() , "stepodooStepApplication", $ostep->getNombre());
			$oPluginRegistry->save();
			return array(
				"success" => true,
				"respuesta" => print_r($ostep)
			);
		} catch(Exception $e) {
			return array( "success" => false, "exception" => $e );
		}
	}
}
