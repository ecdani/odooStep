
<?php
 require_once('/opt/plugins/odooStep/odooStep/dependencies/ripcord/ripcord.php');
 require_once('classes/model/ProcessVariables.php');
/**
 * Step OdooStep App
 * Maneja las operaciones durante la ejecución del paso.
 */
class SosApp {
    protected $ostep, $kwparams, $params, $url,$db,$username,$password,$output,$outputvar;
    protected $refvarm = array(); //Array de referencias a variables múltiple en los kwparams
    protected $valvarm = array(); //Array de valores de las variables múltiple de los kwparams
    protected $keyvarm = array(); //Array de claves de las variables múltiple de los kwparams
    // Almaceno dónde hay una variable múltiple y luego itero todos sus valores en llamadas XML-RPC

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
        
        if (strpos($this->ostep->getMethod(), 'multiple') !== false) { //O si contiene multiple..
            $this->xmlCallmultiple();
        } else {
            $this->xmlCall();
        }
        $this->postprocessMethod();

        $this->saveOutput($this->output);
    }

    /**
     * Get plaintext and divide (without processing variables)
     * Separate v,v,v,... into PHP Array
     */
	public function transformParams($p){
		$p = preg_split("/[\s,]+/", $p);
        foreach ($p as $key => $value) {
            if(is_numeric($value)){
                print_r($value);
                $p[$key] = intval($value);
            }
        }
        return $p;
		//return serialize($parametros);
	}

    /**
     * Get plaintext and divide (without processing variables)
     * Separate k:v,v,v INTRO k:v,.... into PHP KW Array (k keys mus be different or will override.)
     */
	public function transformKWParams($kwp){
		preg_match_all("/([^:\n]+):([^\n]+)/x", $kwp, $p); 
		$kwp = array_combine($p[1], $p[2]);
        return $kwp;
		//return serialize($kwp);
	}

    /**
     * Load Odoo configuration into attributes from database
     */
    public function loadConfig() {
        //$osconf = OdooStepConfPeer::retrieveByPK(1);
        $osconf = $this->Proxy('OdooStepConfPeer','retrieveByPK',1);
        $this->url = $osconf->getUrl();
        $this->db = $osconf->getDb();
        $this->username =	$osconf->getUsername();
        $this->password =	$osconf->getPassword();
    }

    /**
     * Load current step (odooStep) object into attribute
     */
    public function loadOdooStep($uid){ //$_GET['UID']
        $c = new Criteria();
        $c->add(OdooStepStepPeer::STEP_ID, $uid);
        //$ostep = OdooStepStepPeer::doSelectOne($c);
        $this->ostep = $this->Proxy('OdooStepStepPeer','doSelectOne',$c);
    }

    /**
     * Auxiliar function of prepareParams() and callback of preg_replace_callback()
     * Obtain the process maker variable value from global $Fields
     * and replace it in the regular expression
     */
    function varSubtitution($coincidencias) {
        global $Fields;
        return(serialize($Fields["APP_DATA"][$coincidencias[1]])); //Array ( [0] => 8 )
    }

    /**
     * Replace variable @@ expressions with their value, in a PHP variable format
     * also, save reference, key and value of multivalued variables (array, grid)
     * for future iteration
     */
	public function prepareParams($p) {
        //$p = unserialize($p);
        foreach ($p as $key => $value) {
        //print_r("Value");
        //print_r($value);
        //$aux = NULL;
        $aux = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $value);// Notice: Array to string conversion in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 67
        if ($aux != $value) { //varSubtitution substitute sucessfully
            $p[$key] = unserialize($aux);
            if(is_numeric($p[$key])){ //Now determine type, for avoid conflicts with Odoo Api
                $p[$key] = intval($p[$key]);
            }
            if(is_array($p[$key])) { // It's a multivaluated variable (array) , we save it to possibly iterate later.
                    $this->refvarm[] = &$p[$key];
                    $this->keyvarm[] = $key;
                    $this->valvarm[] = $p[$key];
            }
        }
        }
        return $p;
    }
	
    
    /**
     * Replace variable @@ expressions with their value, in a PHP variable format
     * also, save reference, key and value of multivalued variables (array, grid)
     * for future iteration
     * https://stackoverflow.com/questions/14472380/php-store-array-in-array-by-reference
     */
    public function prepareKWParams($kwp) {// Eliminada capacidad para reeplazar variables en claves.
        //$kwp = unserialize($kwp);
        //$keys = array_keys($kwp);
        //$values = array_values($kwp);

        foreach ($kwp as $key => $value) {
          
        
        // TODO: FUSIONAR LOS BUCLES PARA GUARDAR LAS CLAVES DE DONDE ESTAN LAS VARIABLES MULTIPLES Y USARLAS DE ID PARA EL CAMPO DEL GRID
        // PORUQE LA VARIABLE MULTIPLE VA A TRABAJAR EN MODO GRID. 
        // Si, pues en Read no hay claves.... porque usa parametros... no kwparams

            $newValue = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $value);
            if ($newValue != $value) {
                $kwp[$key] = unserialize($newValue);
                if(is_array($kwp[$key])) { // Es una variable múltiple (un array), la guardamos para posiblemente iterar luego.
                    $this->refvarm[] = &$kwp[$key];
                    $this->keyvarm[] = $key;
                    $this->valvarm[] = $kwp[$key];
                }
            }

        }

        //$newKeys = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $keys);
        //$newValues = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $values);
        //$kwp = array_combine($keys, $values);
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
        
       
        return array(array($fparams),$kwp);
    }

    /**
     * $models->execute_kw($db, $uid, $password,'res.partner', 'read', 
     * array($ids),
     * array('fields'=>array('name', 'country_id', 'comment')));
     */
    public function preprocessRead($p,$kwp) {

        $p = $this->transformParams($p);
        $p = $this->prepareParams($p);

        $id_array_from_grid = array();
        echo ("preprocessRead params:");
        echo ("<pre>");
        print_r($p); 
        echo ("</pre>");  
        foreach ($p as $clave => $valor) {
                if(is_array($valor)) {// Si es es una sustitución por una variable múltiple... (i still need to distinguish array from grid, only grid for now)
                    foreach($valor[1] as $key => $value) {
                        if (preg_match("/(_id$|^id$)/",$key)){ //Im not proud of this... they will find the first "id field" like and use it.
                            $id_field = $key;
                        }
                    }
                    foreach($valor as $key => $value) {
                        $id_array_from_grid[] = intval($value[$id_field]); // intval: Odoo complains otherwise.
                    }


                }
        }
       
        $p = array($id_array_from_grid);

        /*echo ("preprocessRead params procesados:");
        echo ("<pre>");
        print_r($p); 
        echo ("</pre>");  */

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
                if(!is_array($valor)) {// Si no es una sustitución por una variable múltiple...
                    $p[$clave] = preg_split("/[,]+/x",$valor);
                    if (count($p[$clave]) == 1) {
                        $p[$clave] = $p[$clave][0];
                    }
                }
            }
            return array(array($p),NULL);
    }

    /**
     * $id = $models->execute_kw($db, $uid, $password, 'res.partner', 'create',
     * array(array('name'=>"New Partner")));
     */
    public function preprocessCreateMultiple($p,$kwp){
            $p = $this->transformKWParams($p);
            $p = $this->prepareKWParams($p);
        
            //preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $p, $par); // Separación k:v,v,v INTRO k:v,....
            //$p = array_combine($par[1], $par[2]);
            foreach ($p as $clave => $valor) {
                $p[$clave] = preg_split("/[,]+/x",$valor);
                if (count($p[$clave]) == 1) { // Si no había array, ...
                    $p[$clave] = $p[$clave][0];
                }
                
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
            case "create_multiple":
                list($p,$kwp) = $this->preprocessCreateMultiple($p,$kwp);
                break;
            case "write":
                list($p,$kwp) = $this->preprocessWrite($p,$kwp);
                break;
            case "unlink":
                list($p,$kwp) = $this->preprocessUnlink($p,$kwp);
                break;
        }
        if (!is_null($p)) {
            $this->params = $p;
        }
        if (!is_null($kwp)) {
            $this->kwparams = $kwp;
        }
    }

    //TODO: Dividir xmlCall y xmlCallMultiple
    public function xmlCall(){
        $common = ripcord::client("$this->url/xmlrpc/2/common"); /*Fatal error: Class 'ripcord' not found in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 30*/
        $uid = $common->authenticate($this->db, $this->username, $this->password, array());
        // Acceso al endpoint de objetos y ejecución de una kw
        $models = ripcord::client("$this->url/xmlrpc/2/object");
        $this->output = $models->execute_kw($this->db, $uid, $this->password,$this->ostep->getModel(),$this->ostep->getMethod(),$this->params, $this->kwparams);
    }

    public function xmlCallMultiple(){
        $common = ripcord::client("$this->url/xmlrpc/2/common"); /*Fatal error: Class 'ripcord' not found in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 30*/
        $uid = $common->authenticate($this->db, $this->username, $this->password, array());
        // Acceso al endpoint de objetos y ejecución de una kw
        $models = ripcord::client("$this->url/xmlrpc/2/object");

        $this->output = array();
        foreach($this->valvarm[0] as $k1 => $v1) { // necesariamente varias variables multiples deben tener el mismo tamaño. Seria absurdo que no.
            foreach($this->valvarm as $k2 => $v2) {
                $this->refvarm[$k2] = $this->valvarm[$k2][$k1][$this->keyvarm[$k2]];
                //k2 = @@grid, k1 = linea keyvarm= @@grid[key]
            }
            /*$output = $models->execute_kw($db, $uid, $password, 'purchase.order.line', 'create',
                    array(array( 'name'=>'[CARD] Graphics Card','price_unit'=>876,'product_uom'=>1,'date_planned'=>'2018-11-12 16:32:13','product_id'=>29,'product_qty'=> 616, 'order_id'=> 10)));
            */
            $this->output[] = $models->execute_kw($this->db, $uid, $this->password,$this->ostep->getModel(),'create',$this->params, $this->kwparams);
        }
    }

    public function postprocessMethod(){



        /*echo ("this->output:");
        echo ("<pre>");
        print_r($this->output); 
        echo ("</pre>"); */
        $c = new Criteria();
        $c->add(ProcessVariablesPeer::VAR_NAME, $this->ostep->getOutput());
        //$ostep = OdooStepStepPeer::doSelectOne($c);
        $this->outputvar = $this->Proxy('ProcessVariablesPeer','doSelectOne',$c);
        /*echo ("pvar:");
        echo ("<pre>");
        print_r($this->outputvar); 
        echo ("</pre>"); */
        $this->outputvar->getVarFieldType();

        

        switch($this->ostep->getMethod()) {
            case "search":
            case "search_count":
                break;
            case "read":
                switch($this->outputvar->getVarFieldType()) {
                    case "grid":
                        $this->output =  $this->postprocessReadGrid($this->output);
                        break;
                    case "array":
                        $this->output =  $this->postprocessReadArray($this->output);
                        break;
                    default:
                        $this->output =  $this->postprocessSearchReadSimple($this->output);
                        break;
                    }
                break;
            case "fields_get":
                break;
            case "search_read":
                switch($this->outputvar->getVarFieldType()) {
                    case "grid":
                        $this->output =  $this->postprocessSearchReadGrid($this->output);
                        break;
                    case "array":
                        $this->output =  $this->postprocessSearchReadArray($this->output); //array
                        break;
                    default:
                        $this->output =  $this->postprocessSearchReadSimple($this->output);
                        break;
                }
                break;
            case "create":
                break;
            case "write":
                break;
            case "unlink":
                break;
        }
    }

    /*
    Formato de entrada:
    Array (
         [0] => Array
             (  [id] => 8
                [name] => Agrolait )
     )
    Formato de salida:
    Array
    (
        [0] => 1
        [1] => d
    )*/
    public function postprocessReadArray($output) {
        foreach ($output as $key => $value) {
            unset($value["id"]);
            $output[$key] = reset($value);
        }
        return $output;
    }

    

     public function postprocessReadGrid($output){
        return array_combine(range(1, count($output)), array_values($output));
     }

    /**
     * Prepare output for textbox
     * Formato entrada: Array (
     *    [0] => Array
     *        (  [id] => 8
     *           [name] => Agrolait )
     *)
     * Formato de salida: "valor"
     */
    public function postprocessSearchReadSimple($output){
        if (count($output[0]) > 1){
            unset($output[0]['id']);
            $aux = array_values($output[0]);
            return $aux[0];
        } else {
            return $output[0]['id'];
        }
    }

    /**
     * De momento un postprocesamiento muy específico
     * Prepare output for drowdown (array PM)
     * Formato: array(array("clave","valor"),array("clave2","valor2"));
     * Array(
     *[0] => Array
     *   (
     *       [description] => Ice cream can be mass-produced and thuents.
     *       [id] => 38
     *      [name] => Ice Cream
     *   )
     */
    public function postprocessSearchReadGrid($output){
        /*$aux = array();
        while (!empty($output)) {
                $e = array_shift ( $output );
                $aux[] = array($e['id'],$e[$this->kwparams['fields'][0]]); //$this->kwparams['fields'][0]
            }*/
        return $output;
    }


    /**
     * De momento un postprocesamiento específico
     * Prepare output for drowdown (array PM)
     * Formato entrada: array(array("clave","valor"),array("clave2","valor2"));
     * Formato de salida: Array(
     *[0] => Array
     *   (
     *       [description] => Ice cream can be mass-produced and thuents.
     *       [id] => 38
     *      [name] => Ice Cream
     *   )
     */
    public function postprocessSearchReadArray($output){
        $aux = array();
        while (!empty($output)) {
                $e = array_shift ( $output );
                $id = $e['id'];
                unset($e['id']);
                $e = array_values($e);
                array_unshift($e,$id);
                $aux[] = $e;
            }
        return $aux;
    }

    // Salvando la salida en la variable indicada.
    // Tomo la siguiente decisión:
    // Las variables no se sobreescriben, sino que se combinan por defecto.
    // Tal vez un  combobox eligiendo entre sobreescribir o actualizar 
    // al lado de "Salida" en el formulario de creacion de step
    // haga más potente el software.
    public function saveOutput($output){
        global $Fields;
        $case = new Cases();
        $loaded = $case->loadCase($Fields["APP_UID"]);

        switch($this->outputvar->getVarFieldType()) {
                    case "grid":
                    //$loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        foreach ($output as $k => $v) {
                            foreach ($output[$k] as $k2 => $v2) {
                                $loaded["APP_DATA"][$this->ostep->getOutput()][$k][$k2] = $v2;
                            }
                        }
                        //$loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        break;
                    case "array":
                        $loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        break;
                    default:
                        $loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        break;
                }

        
        
        /*echo ("this->params:");
        echo ("<pre>");
        print_r($this->params); 
        echo ("</pre>");  

        echo ("output:");
        echo ("<pre>");
        print_r($output); 
        echo ("</pre>");  
       
       echo ("Partner:");
        echo ("<pre>");
        print_r($loaded["APP_DATA"]['partner']); //
        echo ("</pre>");  

        echo ("itemGrid:");
        echo ("<pre>");
        print_r($loaded["APP_DATA"]['itemGrid']); 
        echo ("</pre>"); 

        echo ("gridload:");
        echo ("<pre>");
        print_r($loaded["APP_DATA"]['gridload']); 
        echo ("</pre>"); 

        echo ("checkgroup:");
        echo ("<pre>");
        print_r($loaded["APP_DATA"]['checkgroup']); 
        echo ("</pre>"); 

        echo ("testarrayprecios:");
        echo ("<pre>");
        print_r($loaded["APP_DATA"]['testarrayprecios']); 
        echo ("</pre>"); */

        /*
        Array
        (
            [1] => Array
                (
                    [text0000000001] => 
                    [text0000000001_label] => 
                    [text0000000002] => 17
                    [text0000000002_label] => 17
                    [text0000000003] => desc
                    [text0000000003_label] => desc
                )
        )        
        */

        $case->updateCase($Fields["APP_UID"], $loaded);
    }
}