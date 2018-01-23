<?php

/**
 * Step Configuration App
 * Creación, Edición, Borrado y Listado de OdooSteps
 */
class scApp {

	/**
	 * Factory method, los métodos estáticos no pueden emularse.
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
	 * Proxy para las clases Peer y evitar conflicto con
	 * las llamadas estáticas en el testing de SimpleTest.
	 */
    protected function proxy($c,$f,$p) {
		return call_user_func_array($c.'::'.$f, array($p)); 
	}

	/**
	 * Obtiene una lista de OdooSteps preparada para ser mostrada
	 * en un formulario ExtJS.
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
			//$p = $this->revertParams($v->getParameters());
			//$kwp = $this->revertKWParams($v->getKwParameters());
			
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

	public function saveStep($post) {
		try {
			//$ostep = OdooStepStepPeer::retrieveByPK($stepid);
			$ostep = $this->proxy('OdooStepStepPeer','retrieveByPK',$post["id"]);
			if (!(is_object($ostep) && get_class($ostep) == 'OdooStepStep')) {
				$ostep = $this->f("OdooStepStep");
				//$ostep = new OdooStepStep();
				$ostep->setStepId($post["id"]);
			}

			$ostep->setNombre($post["newNombre"]);
			$ostep->setProUid($post["newProceso"]);
			$ostep->setModel($post["newModelo"]);
			$ostep->setMethod($post["newMetodo"]);
			$ostep->setOutput($post["newSalida"]);
			//$parametros = $this->transformParams($post["newParametros"]);
			//$kwparams = $this->transformKWParams($post["newParametrosKW"]);
			$ostep->setParameters($post["newParametros"]);
			$ostep->setKwParameters($post["newParametrosKW"]);
			$ostep->save();
			return array("success" => true, "respuesta" => $ostep);
		} catch (Exception $e) {
			return array("success" => false, "exception" => $e);
		}
		
	}

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
		// echo "{success: " . true . ", resultTotal: " . count($user) . ", resultRoot: " . G::json_encode($user) . "}";
		} catch (Exception $e){
			return array("success" => false, "exception" => $e);
		}
	}

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
