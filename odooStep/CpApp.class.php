<?php

require_once ("classes/model/OdooStepConf.php");
require_once ("classes/model/OdooStepConfPeer.php");
Propel::init(PATH_CORE . "config/databases.php");

/**
 * Configuration Page App
 * Load and store Odoo configuration parameters
 */
class cpApp {

	/**
	 * Factory method, because static methods can not be emulated.
	 * @param $p Name of class
	 */
	protected function f($p) {
		switch ($p) {
            case "OdooStepConf":
            	return new OdooStepConf();
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
	 * Saves Odoo configuration parameters from "Odoo Config" page
	 * @param $post HTTP POST method result
	 */
	public function saveConf($post) {
		try {
			//$osconf = OdooStepConfPeer::retrieveByPK(1);
			$osconf = $this->proxy('OdooStepConfPeer','retrieveByPK',1);
			if (is_null($osconf)) $osconf = $this->f("OdooStepConf"); 
			if (isset($post['txtUrl'])) $osconf->setUrl($post["txtUrl"]); else throw new Exception("URL no proporcionada",1);
			if (isset($post['txtDb'])) $osconf->setDb($post["txtDb"]); else throw new Exception("DB no proporcionada",1);
			if (isset($post['txtUsuario'])) $osconf->setUsername($post["txtUsuario"]); else throw new Exception("Usuario no proporcionado",1);
			if (isset($post['txtPassword'])) $osconf->setPassword($post["txtPassword"]); else throw new Exception("Password no proporcionado",1);

			$osconf->save();
			return G::json_encode(array( "success" => true, "respuesta" => NULL	));
		} catch(Exception $e) {
			return G::json_encode(array(
				"success" => false,
				"respuesta" => $e->getMessage()
			));
		}
	}

	/**
	 * Loads Odoo configuration parameters from database for "Odoo Config" page
	 */
	public function loadConf() {
		try{
			//$OdooStepConf = OdooStepConfPeer::retrieveByPK(1);
			$OdooStepConf = $this->proxy('OdooStepConfPeer','retrieveByPK',1);
			
			if (! is_null ( $OdooStepConf ) ) {
				$array = $OdooStepConf->toArray();
			} else {
				$array = array();
				$array["Url"] = NULL;
				$array["Db"] = NULL;
				$array["Username"] = NULL;
			}
			return $array;
		} catch (Exception $e) {
			return array(
					"success" => false,
					"respuesta" => $e->getMessage()
				);
		}
	}
}