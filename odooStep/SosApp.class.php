
<?php
 require_once('/opt/plugins/odooStep/odooStep/dependencies/ripcord/ripcord.php');
/**
 * Step OdooStep App
 * Maneja las operaciones durante la ejecución del paso.
 */
class SosApp {
    protected $ostep, $kwparams, $params, $url,$db,$username,$password,$output;

    /**
	* Factory method, los métodos estáticos no pueden emularse.
	*/
	protected function f($p) {
		switch ($p) {
            case "OdooStepConf":
            	return new OdooStepConf();
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

    public function execute($uid){ //$_GET['UID']
        $this->loadOdooStep($uid);
        //$this->ostep->setParameters($this->transformParams($this->ostep->getParameters()));
        //$this->ostep->setKWParameters($this->transformKWParams($this->ostep->getKwParameters()));

        //$this->prepareParams($this->ostep->getParameters());
        //$this->prepareKWParams($this->ostep->getKwParameters());
        $this->loadConfig();
        $this->preprocessMethod($this->ostep->getMethod());
        $this->xmlCall();
        $this->postprocessMethod();

        $this->saveOutput($this->output);
    }

    // Separación v,v,v,...
	public function transformParams($p){
		$p = preg_split("/[\s,]+/", $p);
        return $p;
		//return serialize($parametros);
	}

	// Separación k:v,v,v INTRO k:v,.... que no sean iguales la k.
	public function transformKWParams($kwp){
		preg_match_all("/([^:\n]+):([^\n]+)/x", $kwp, $p); 
		$kwp = array_combine($p[1], $p[2]);
        return $kwp;
		//return serialize($kwp);
	}

    /* Cargamos la configuración básica*/
    public function loadConfig() {
        //$osconf = OdooStepConfPeer::retrieveByPK(1);
        $osconf = $this->Proxy('OdooStepConfPeer','retrieveByPK',1);
        $this->url = $osconf->getUrl();
        $this->db = $osconf->getDb();
        $this->username =	$osconf->getUsername();
        $this->password =	$osconf->getPassword();
    }

    public function loadOdooStep($uid){ //$_GET['UID']
        $c = new Criteria();
        $c->add(OdooStepStepPeer::STEP_ID, $uid);
        //$ostep = OdooStepStepPeer::doSelectOne($c);
        $this->ostep = $this->Proxy('OdooStepStepPeer','doSelectOne',$c);
    }

    function varSubtitution($coincidencias) {
        global $Fields;
        return(serialize($Fields["APP_DATA"][$coincidencias[1]])); //Array ( [0] => 8 )
    }


	public function prepareParams($p) {
        //$p = unserialize($p);
        foreach ($p as $key => $value) {
        //print_r("Value");
        //print_r($value);
        //$aux = NULL;
        $aux = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $value);// Notice: Array to string conversion in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 67
        if ($aux != $value) {$p[$key] = unserialize($aux);}
        }
        return $p;
    }
	
    public function prepareKWParams($kwp) {
        //$kwp = unserialize($kwp);
        $keys = array_keys($kwp);
        $values = array_values($kwp);
        $newKeys = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $keys);
        $newValues = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $values);
        $kwp = array_combine($newKeys, $newValues);
        return $kwp;
    }

    /** 
     * Formato llamada API XML-RPC de Odoo:
     * $models->execute_kw($db, $uid, $password,'res.partner', 'search',
     * array(array(array('is_company', '=', true),array('customer', '=', true))),
     * array('offset'=>10, 'limit'=>5));
     * $models->execute_kw($db, $uid, $password,'res.partner', 'search_count',
     * array(array(array('is_company', '=', true),array('customer', '=', true))));
     */
    public function preprocessSearch($p,$kwp) {
        if($p != "") {
            $p = $this->transformParams($p);
            $p = $this->prepareParams($p);
            while (!empty($p)) {
                $aux = array();
                $aux[] =  array_shift ( $p );
                $aux[] =  array_shift ( $p );
                $aux[] =  array_shift ( $p );
                $fparams[] = $aux;
            }
        } else {
            $fparams = array();
        }
        
        $kwp = $this->transformKWParams($kwp);
        
        $kwp = $this->prepareKWParams($kwp);
        
       
        return array($fparams,$kwp);
    }

    /**
     * $models->execute_kw($db, $uid, $password,'res.partner', 'read', 
     * array($ids),
     * array('fields'=>array('name', 'country_id', 'comment')));
     */
    public function preprocessRead($p,$kwp) {

        $p = $this->transformParams($p);
        $p = $this->prepareParams($p);

        $kwp = $this->transformKWParams($kwp);
        $kwp = $this->prepareKWParams($kwp);

        foreach ($kwp as $clave => $valor) {
            $kwp[$clave] = preg_split("/[,]+/x",$valor);// Rotura en array de los parametros: array('name', 'country_id', 'comment')
        }
        return array($p,$kwp);
    }

    /**
     * En teoria no hay parametros, solo kwparams
     * $models->execute_kw($db, $uid, $password,'res.partner', 'fields_get', 
     * array(),
     * array('attributes' => array('string', 'help', 'type')));
     */
    public function preprocessFieldsGet($kwp) {
        $kwp = $this->transformKWParams($kwp);
        $kwp = $this->prepareKWParams($kwp);

        foreach ($kwp as $clave => $valor) {
            $kwp[$clave] = preg_split("/[,]+/x",$valor);// Rotura en array de los parametros: array('name', 'country_id', 'comment')
        }
        return array(array(),$kwp);
    }

    /**
     * $models->execute_kw($db, $uid, $password,'res.partner', 'search_read',
     * array(array(array('is_company', '=', true), array('customer', '=', true))),
     * array('fields'=>array('name', 'country_id', 'comment'), 'limit'=>5));
     */
    public function preprocessSearchRead($p,$kwp){
        if($p != "") {
            $p = $this->transformParams($p);
            $p = $this->prepareParams($p);
            while (!empty($p)) {
                $aux = array();
                $aux[] =  array_shift ( $p );
                $aux[] =  array_shift ( $p );
                $aux[] =  array_shift ( $p );
                $fparams[] = $aux;
            }
            $fparams = array($fparams);
        } else {
            $fparams = array();
        }

        $kwp = $this->transformKWParams($kwp);
        $kwp = $this->prepareKWParams($kwp);
        foreach ($kwp as $clave => $valor) {
            $kwp[$clave] = preg_split("/[,]+/x",$valor);
        }
        return array($fparams,$kwp);
    }

    /**
     * $id = $models->execute_kw($db, $uid, $password, 'res.partner', 'create',
     * array(array('name'=>"New Partner")));
     */
    public function preprocessCreate($p,$kwp){
            $p = $this->transformKWParams($p);
            $p = $this->prepareKWParams($p);
        
            //preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $p, $par); // Separación k:v,v,v INTRO k:v,....
            //$p = array_combine($par[1], $par[2]);
            foreach ($p as $clave => $valor) {
                $p[$clave] = preg_split("/[,]+/x",$valor);
            }
            return array(array($p),NULL);
    }

    /**
     * $models->execute_kw($db, $uid, $password, 'res.partner', 'write',
     * array(array($id), array('name'=>"Newer partner")));
     * El formato será igual que el KW
     * ids:7,5,4,6
     * name:Manolo
     */
    public function preprocessWrite($p,$kwp) {
            $p = $this->transformKWParams($p);
            $p = $this->prepareKWParams($p);

           // preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $_POST["newParametros"], $p); // Separación k:v,v,v INTRO k:v,....
            //$parametros = array_combine($p[1], $p[2]);
            $p[0] = preg_split("/[,]+/x",$p["ids"]);
            unset($p["ids"]);
            $fparams = array($p);
            return array($fparams,NULL);
    }

    /**
     * $models->execute_kw($db, $uid, $password, 'res.partner', 'unlink',
     * array(array($id)));
     */
    public function preprocessUnlink($p,$kwp) {
            return array(array(array($p)),NULL);
    }

    public function preprocessMethod($method){
        $p = $this->ostep->getParameters();
        $kwp = $this->ostep->getKwParameters();
        switch($this->ostep->getMethod()) {
            case "search":
            case "search_count":
                list($p,$kwp) = $this->preprocessSearch($p,$kwp);
                break;
            case "read":
                list($p,$kwp) = $this->preprocessRead($p,$kwp);
                break;
            case "fields_get":
                list($p,$kwp) = $this->preprocessFieldsGet($kwp);
                break;
            case "search_read":
                list($p,$kwp) = $this->preprocessSearchRead($p,$kwp);
                break;
            case "create":
                list($p,$kwp) = $this->preprocessCreate($p,$kwp);
                break;
            case "write":
                list($p,$kwp) = $this->preprocessWrite($p,$kwp);
                break;
            case "unlink":
                list($p,$kwp) = $this->preprocessWrite($p,$kwp);
                break;
        }
        if (!is_null($p)) {
            $this->params = $p;
        }
        if (!is_null($kwp)) {
            $this->kwparams = $kwp;
        }
    }

    public function xmlCall(){
        $common = ripcord::client("$this->url/xmlrpc/2/common"); /*Fatal error: Class 'ripcord' not found in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 30*/
        $uid = $common->authenticate($this->db, $this->username, $this->password, array());
        // Acceso al endpoint de objetos y ejecución de una kw
        $models = ripcord::client("$this->url/xmlrpc/2/object");
        $this->output = $models->execute_kw($this->db, $uid, $this->password,$this->ostep->getModel(),$this->ostep->getMethod(),$this->params, $this->kwparams);
    }

    public function postprocessMethod(){
        switch($this->ostep->getMethod()) {
            case "search":
            case "search_count":
                break;
            case "read":
                break;
            case "fields_get":
                break;
            case "search_read":
                $this->output =  $this->postprocessSearchRead($this->output);
                break;
            case "create":
                break;
            case "write":
                break;
            case "unlink":
                break;
        }
    }

    /**
     * De momento un postprocesamiento muy específico
     * Prepare output for drowdown
     * Formato: array(array("clave","valor"),array("clave2","valor2"));
     * Array(
     *[0] => Array
     *   (
     *       [description] => Ice cream can be mass-produced and thuents.
     *       [id] => 38
     *      [name] => Ice Cream
     *   )
     */
    public function postprocessSearchRead($output){
        $aux = array();
        while (!empty($output)) {
                $e = array_shift ( $output );
                $aux[] = array($e['id'],$e['name']);
            }
        return $aux;
    }

    // Salvando la salida en la variable indicada.
    public function saveOutput($output){
        global $Fields;
        $case = new Cases();
        $loaded = $case->loadCase($Fields["APP_UID"]);
        $loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
       
        echo ("<pre>");
        print_r($loaded["APP_DATA"][$this->ostep->getOutput()]); 
        echo ("</pre>"); 
        $case->updateCase($Fields["APP_UID"], $loaded);
    }
}