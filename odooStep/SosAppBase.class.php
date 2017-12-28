
<?php
 require_once('dependencies/ripcord/ripcord.php');
 require_once('classes/model/ProcessVariables.php');

/**
 * Step OdooStep App
 * Maneja las operaciones durante la ejecución del paso.
 */
class BaseSosApp {
    protected $ostep, $kwparams, $params, $url,$db,$username,$password,$output,$outputvar;
    //protected $refvarm = array(); //Array de referencias a variables múltiple en los kwparams
    //protected $valvarm = array(); //Array de valores de las variables múltiple de los kwparams
    //protected $keyvarm = array(); //Array de claves de las variables múltiple de los kwparams
    protected $mvar = array(); // Array de metainformación de las variables multiples.
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
     * Extract the array that conforms a single field in a PM grid variable type
     */
    function grid_field_to_array($grid,$field){
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
     * Obtain the process maker variable value from global $Fields
     * and replace it in the regular expression
     */
    function varSubtitution($coincidencias) {


        global $Fields;

        /*echo ("coincidencias:");
        echo ("<pre>");
        print_r($coincidencias); 
        echo ("</pre>");*/
        $var = array();
        $var[0] = $coincidencias[1];
        $var[1] = null;
        $var[2] = $Fields["APP_DATA"][$coincidencias[1]];

        if (!empty($coincidencias[2])){
            $var[1] = $coincidencias[2];
            $var[2] = $this->grid_field_to_array($var[2],$coincidencias[2]);
        }

        /*echo ("var in varSubstitution:");
        echo ("<pre>");
        print_r($var); 
        echo ("</pre>");*/

        return(serialize($var)); //Array ( [0] => 8 )
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
            $newValue = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)[\[\]]*(\w*)[\]]*/", array($this, 'varSubtitution'), $value);// Notice: Array to string conversion in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 67
            // "/@[@%#\?\x24\=]([A-Za-z_]\w*)[\[\]]*(\w*)[\]]*/"
            //"/@[@%#\?\x24\=]([A-Za-z_]\w*)/"
            if ($newValue != $value) { //varSubtitution substitute sucessfully
                $newValue = unserialize($newValue);
                $p[$key] = $newValue[2];

                if(is_numeric($p[$key])){ //Now determine type, for avoid conflicts with Odoo Api
                    $p[$key] = intval($p[$key]);
                }
                if(is_array($p[$key])) { // It's a multivaluated variable (array) , we save it to possibly iterate later.

                        /*if (isset($newValue[1])) {
                            $this->keyvarm[] = $newValue[0];
                        } else {
                            $this->keyvarm[] = $key;
                        }
                        $this->refvarm[] = &$p[$key];
                        $this->valvarm[] = $p[$key];*/

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
     */
    public function prepareKWParams($kwp) {// Eliminada capacidad para reeplazar variables en claves.
        //$kwp = unserialize($kwp);
        //$keys = array_keys($kwp);
        //$values = array_values($kwp);

        foreach ($kwp as $key => $value) {
          
        
        // TODO: FUSIONAR LOS BUCLES PARA GUARDAR LAS CLAVES DE DONDE ESTAN LAS VARIABLES MULTIPLES Y USARLAS DE ID PARA EL CAMPO DEL GRID
        // PORUQE LA VARIABLE MULTIPLE VA A TRABAJAR EN MODO GRID. 
        // Si, pues en Read no hay claves.... porque usa parametros... no kwparams

            $newValue = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)[\[\]]*(\w*)[\]]*/", array($this, 'varSubtitution'), $value);
            //"/@[@%#\?\x24\=]([A-Za-z_]\w*)[\[\]]*(\w*)[\]]*/"
            //"/@[@%#\?\x24\=]([A-Za-z_]\w*)/"
            if ($newValue != $value) {
                $newValue = unserialize($newValue);
                $kwp[$key]  = $newValue[2];
                if(is_array($kwp[$key])) { // Es una variable múltiple (un array), la guardamos para posiblemente iterar luego.
                    //$this->refvarm[] = &$kwp[$key];
                    //$this->keyvarm[] = $key;
                    ///$this->valvarm[] = $kwp[$key];

                    $this->mvar['pkey'][] = $key; // Parameter key, example;   PKEY:name_of_var[field]
                    $this->mvar['ref'][] = &$kwp[$key]; 
                    $this->mvar['value'][] = $kwp[$key]; 
                    $this->mvar['name'][] = $newValue[0]; // variable name, example;   pkey:NAME[field]
                    $this->mvar['field'][] = $newValue[1]; // field name of grid variable, example;   pkey:grid_var_name[FIELD]

                }
            }

        }

        //$newKeys = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $keys);
        //$newValues = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", array($this, 'varSubtitution'), $values);
        //$kwp = array_combine($keys, $values);
        return $kwp;
    }

    /**
     * Hook for preprocess methods
     */
    public function preprocessMethod($method){
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


        // necesariamente varias variables multiples deben tener el mismo tamaño. Seria absurdo que no.

        /*echo ("this->mvar['value']:");
        echo ("<pre>");
        print_r($this->mvar['value']); 
        echo ("</pre>");*/
        

        foreach($this->mvar['value'][0] as $field => $v1) { // Iteracion sobre campos
            foreach($this->mvar['value'] as $mvpkey => $value) { // Iteracion sobre la grilla
                
                /*echo ("this->mvar['field'][mvpkey]:");
                echo ("<pre>");
                print_r($mvpkey); 
                print_r($this->mvar['field']); 
                echo ("</pre>");*/

                if (!is_null($this->mvar['field'][$mvpkey])) {
                    //[$this->mvar['field'][$mvpkey]]
                    $this->mvar['ref'][$mvpkey] = $this->mvar['value'][$mvpkey][$field];
                } else {
                    //[$this->mvar['pkey'][$mvpkey]]
                    $this->mvar['ref'][$mvpkey] = $this->mvar['value'][$mvpkey][$field];
                }
                
                //k2 = @@grid, k1 = linea keyvarm= @@grid[key]
            }
            /*$output = $models->execute_kw($db, $uid, $password, 'purchase.order.line', 'create',
                    array(array( 'name'=>'[CARD] Graphics Card','price_unit'=>876,'product_uom'=>1,'date_planned'=>'2018-11-12 16:32:13','product_id'=>29,'product_qty'=> 616, 'order_id'=> 10)));
            */
            $this->output[] = $models->execute_kw($this->db, $uid, $this->password,$this->ostep->getModel(),'create',$this->params, $this->kwparams);
        }
    }

    public function postprocessMethod(){

        $c = new Criteria();
        $c->add(ProcessVariablesPeer::VAR_NAME, $this->ostep->getOutput());
        //$ostep = OdooStepStepPeer::doSelectOne($c);
        $this->outputvar = $this->Proxy('ProcessVariablesPeer','doSelectOne',$c);
        /*echo ("pvar:");
        echo ("<pre>");
        print_r($this->outputvar); 
        echo ("</pre>"); */
        //$this->outputvar->getVarFieldType();

        if  (!is_null ( $this->outputvar ) ) {
            $method = "postprocess_" . $this->ostep->getMethod(). "_" . $this->outputvar->getVarFieldType();
            if (method_exists($this,$method)) {
                $this->output = $this->$method($this->output);
            }
        }
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
        if  (!is_null ( $this->outputvar ) ) {

            switch($this->outputvar->getVarFieldType()) {
                    case "grid":
                    //$loaded["APP_DATA"][$this->ostep->getOutput()] = $output;
                        foreach ($output as $k => $v) {
                            foreach ($output[$k] as $k2 => $v2) { //FALLOoooooooooo Invalid argument supplied for foreach() in /opt/plugins/odooStep/odooStep/SosApp.class.php on line 627
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
        }

        
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

        $case->updateCase($Fields["APP_UID"], $loaded);
    }
}
