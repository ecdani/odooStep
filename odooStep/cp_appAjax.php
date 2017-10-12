<?php
/**
 * Callback de las peticiones ajax de la página de config.
 */
require_once ("classes/model/OdooStepConf.php");
require_once ("classes/model/OdooStepConfPeer.php");
Propel::init(PATH_CORE . "config/databases.php");

class cpAppAjax {

	/**
	* Factory method
	*/
	protected function newOdooStepConf() {
        return new OdooStepConf();
    }

	public function saveConf($post) {
			try {
				$osconf = OdooStepConfPeer::retrieveByPK(1);
				if (is_null($osconf)) $osconf = $this->newOdooStepConf(); 
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
}

if (isset($_POST["option"]) && $_POST["option"] == "SAVE") {
	$o = new cpAppAjax();
	echo $o->saveConf($_POST);
}

?>