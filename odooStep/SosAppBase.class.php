
<?php
 require_once('dependencies/ripcord/ripcord.php');
 require_once('classes/model/ProcessVariables.php');

/**
 * Step OdooStep App
 * Maneja las operaciones durante la ejecución del paso.
 */
class BaseSosApp {
    public $ostep, $kwparams, $params, $url,$db,$username,$password,$output,$outputvar;
    public $mvar = array(); // Array de metainformación de las variables multiples.
    // Almaceno dónde hay una variable múltiple y luego itero todos sus valores en llamadas XML-RPC

    /**
	* Factory method, los métodos estáticos no pueden emularse.
	*/
	protected function f($p) {
		switch ($p) {
            case "OdooStepConf":
            	return new OdooStepConf();
            case "Cases":
                return new Cases();
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
     * Ejecucion de un paso de Odoo.
     * @param $uid User id
     */
    public function execute($uid) {
        $this->loadOdooStep($uid);
        $this->loadConfig();
        $this->preprocessMethod();
        
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
	public function transformParams($p) {
		$p = preg_split("/[\s,]+/", $p);
        foreach ($p as $key => $value) {
            if(is_numeric($value)){
                print_r($value);
                $p[$key] = intval($value);
            }
        }
        return $p;
	}

    /**
     * Get plaintext and divide (without processing variables)
     * Separate k:v,v,v INTRO k:v,.... into PHP KW Array (k keys mus be different or will override.)
     */
	public function transformKWParams($kwp) {
		preg_match_all("/([^:\n]+):([^\n]+)/x", $kwp, $p); 
		$kwp = array_combine($p[1], $p[2]);
        return $kwp;
	}

    /**
     * Load Odoo configuration into attributes from database
     */
    public function loadConfig() {
        $osconf = $this->proxy('OdooStepConfPeer','retrieveByPK',1);
        $this->url = $osconf->getUrl();
        $this->db = $osconf->getDb();
        $this->username =	$osconf->getUsername();
        $this->password =	$osconf->getPassword();
    }

    /**
     * Load current step (odooStep) object into attribute
     * @param uid User id
     */
    public function loadOdooStep($uid) {
        $c = new Criteria();
        $c->add(OdooStepStepPeer::STEP_ID, $uid);
        $this->ostep = $this->proxy('OdooStepStepPeer','doSelectOne',$c);
    }

    /**
     * Extract the array that conforms a single field in a PM grid variable type
     * @param $grid PM grid variable
     * @param $field Selected field of the grid
     */
    function grid_field_to_array($grid,$field) {
        $array = array();
        foreach($grid as $key => $value) {
            if(is_numeric($value[$field])){
                $array[] = intval($value[$field]); // intval: Odoo complains otherwise.
            } else {
                $array[] = $value[$field];
            }
        }
        return $array;
    }

    /**
     * Auxiliar function of prepareParams() and callback of preg_replace_callback()
     * Obtain the ProcessMaker variable value from global $Fields
     * and replace it in the regular expression
     * @param $coincidencias Variable coincidences
     */
    function varSubtitution($coincidencias) {
        global $Fields;

        $var = array();
        $var[0] = $coincidencias[1];
        $var[1] = null;
        $var[2] = $Fields["APP_DATA"][$coincidencias[1]];

        if (!empty($coincidencias[2])){
            $var[1] = $coincidencias[2];
            $var[2] = $this->grid_field_to_array($var[2],$coincidencias[2]);
        }
        return(serialize($var)); //Array ( [0] => 8 )
    }

    /**
     * Replace variable @@ expressions with their value, in a PHP variable format
     * also, save reference, key and value of multivalued variables (array, grid)
     * for future iteration
     * @param $p Params array
     */
	public function prepareParams($p) {
        foreach ($p as $key => $value) {
            $newValue = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)[\[\]]*(\w*)[\]]*/", array($this, 'varSubtitution'), $value);
            if ($newValue != $value) { //varSubtitution substitute sucessfully
                $newValue = unserialize($newValue);
                $p[$key] = $newValue[2];

                if(is_numeric($p[$key])){ //Now determine type, for avoid conflicts with Odoo Api
                    $p[$key] = intval($p[$key]);
                }
                if(is_array($p[$key])) { // It's a multivaluated variable (array) , we save it to possibly iterate later.

                    $this->mvar['pkey'][] = $key; // Parameter key, example;   PKEY:name_of_var[field]
                    $this->mvar['ref'][] = &$p[$key]; 
                    $this->mvar['value'][] = $p[$key]; 
                    $this->mvar['name'][] = $newValue[0]; // variable name, example;   pkey:NAME[field]
                    $this->mvar['field'][] = $newValue[1]; // field name of grid variable, example;   pkey:grid_var_name[FIELD]
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
     * @param $kwp Key-value parameters array
     */
    public function prepareKWParams($kwp) {// Achtung: Eliminada capacidad para reeplazar variables en claves.

        foreach ($kwp as $key => $value) {      
            $newValue = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)[\[\]]*(\w*)[\]]*/", array($this, 'varSubtitution'), $value);

            if ($newValue != $value) {
                $newValue = unserialize($newValue);
                $kwp[$key]  = $newValue[2];
                if(is_array($kwp[$key])) { // Es una variable múltiple (un array), la guardamos para posiblemente iterar luego.

                    $this->mvar['pkey'][] = $key; // Parameter key, example;   PKEY:name_of_var[field]
                    $this->mvar['ref'][] = &$kwp[$key]; 
                    $this->mvar['value'][] = $kwp[$key]; 
                    $this->mvar['name'][] = $newValue[0]; // variable name, example;   pkey:NAME[field]
                    $this->mvar['field'][] = $newValue[1]; // field name of grid variable, example;   pkey:grid_var_name[FIELD]

                }
            }
        }
        return $kwp;
    }

    /**
     * Hook for preprocess methods
     */
    public function preprocessMethod() {
        $p = $this->ostep->getParameters();
        $kwp = $this->ostep->getKwParameters();

        $method = "preprocess_" . $this->ostep->getMethod();

        if (method_exists($this,$method)) {
            list($p,$kwp) = $this->$method($p,$kwp);
        }

        if (!is_null($p)) {
            $this->params = $p;
        }
        if (!is_null($kwp)) {
            $this->kwparams = $kwp;
        }
    }

    /**
     * Call to XML-RPC Odoo's API
     */
    public function xmlCall() {
        $common = ripcord::client("$this->url/xmlrpc/2/common");
        $uid = $common->authenticate($this->db, $this->username, $this->password, array());
        // Acceso al endpoint de objetos y ejecución de una kw
        $models = ripcord::client("$this->url/xmlrpc/2/object");
        $this->output = $models->execute_kw($this->db, $uid, $this->password,$this->ostep->getModel(),$this->ostep->getMethod(),$this->params, $this->kwparams);
    }

    /**
     * Multiple's version of the call to XML-RPC Odoo's API
     */
    public function xmlCallMultiple() {
        $common = ripcord::client("$this->url/xmlrpc/2/common");
        $uid = $common->authenticate($this->db, $this->username, $this->password, array());
        // Acceso al endpoint de objetos y ejecución de una kw
        $models = ripcord::client("$this->url/xmlrpc/2/object");

        $this->output = array();

        // necesariamente varias variables multiples deben tener el mismo tamaño. Seria absurdo que no.

        foreach($this->mvar['value'][0] as $field => $v1) { // Iteracion sobre campos
            foreach($this->mvar['value'] as $mvpkey => $value) { // Iteracion sobre la grilla
                
                if (!is_null($this->mvar['field'][$mvpkey])) {
                    $this->mvar['ref'][$mvpkey] = $this->mvar['value'][$mvpkey][$field];
                } else {
                    $this->mvar['ref'][$mvpkey] = $this->mvar['value'][$mvpkey][$field];
                }
            }
            $this->output[] = $models->execute_kw($this->db, $uid, $this->password,$this->ostep->getModel(),'create',$this->params, $this->kwparams);
        }
    }

    /**
     * Hook for postprocess methods
     */
    public function postprocessMethod() {
        $c = new Criteria();
        $c->add(ProcessVariablesPeer::VAR_NAME, $this->ostep->getOutput());
        $this->outputvar = $this->proxy('ProcessVariablesPeer','doSelectOne',$c);

        if  (!is_null ( $this->outputvar ) ) {
            $method = "postprocess_" . $this->ostep->getMethod(). "_" . $this->outputvar->getVarFieldType();
            if (method_exists($this,$method)) {
                $this->output = $this->$method($this->output);
            }
        }
    }

    /**
     * Salvando la salida en la variable indicada.
     * @param $output Return of XML-RPC call.
     */
    public function saveOutput($output) {
        global $Fields;
        $case = $this->f("Cases");
        $loaded = $case->loadCase($Fields["APP_UID"]);
        if  (!is_null ( $this->outputvar ) ) {

            /**
             * Tomo la siguiente decisión:
             * Las variables no se sobreescriben, sino que se combinan por defecto.
             * Tal vez un  combobox eligiendo entre sobreescribir o actualizar 
             * al lado de "Salida" en el formulario de creacion de step
             * haga más potente el software.
             */
            switch($this->outputvar->getVarFieldType()) {
                    case "grid":
                        foreach ($output as $k => $v) {
                            foreach ($output[$k] as $k2 => $v2) { 
                                $loaded["APP_DATA"][$this->ostep->getOutput()][$k][$k2] = $v2; // Esta es la combinacion
                            }
                        }
                        break;
                    case "array":
                        $loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        break;
                    default:
                        $loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        break;
                }
        }

        $case->updateCase($Fields["APP_UID"], $loaded);

        # DEPURATION CODE:
        
        /*
        echo ("this->params:");
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

        /*echo ("testarrayprecios:");
        echo ("<pre>");
        print_r($loaded["APP_DATA"]); 
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
    }
}
