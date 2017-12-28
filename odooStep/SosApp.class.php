
<?php
 require_once('dependencies/ripcord/ripcord.php');
 require_once('classes/model/ProcessVariables.php');
 require_once('SosAppBase.class.php');

/**
 * Step OdooStep App
 * Contains preprocessors and postprocessors implementations.
 */
class SosApp extends BaseSosApp {

    /***************PREPROCESSORS**********************/

    /** 
     * Formato llamada API XML-RPC de Odoo:
     * $models->execute_kw($db, $uid, $password,'res.partner', 'search',
     * array(array(array('is_company', '=', true),array('customer', '=', true))),
     * array('offset'=>10, 'limit'=>5));
     * $models->execute_kw($db, $uid, $password,'res.partner', 'search_count',
     * array(array(array('is_company', '=', true),array('customer', '=', true))));
     */
    public function preprocess_search($p,$kwp) {
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
    public function preprocess_read($p,$kwp) {

        $p = $this->transformParams($p);
        $p = $this->prepareParams($p);

        //$id_array_from_grid = array();
        /*echo ("preprocessRead params:");
        echo ("<pre>");
        print_r($p); 
        echo ("</pre>");*/
        // Ahora se exige un array, debido a que se puede especificar el campo del grid.
        /*  
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
        }*/
       
        //$p = array($id_array_from_grid);

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
    public function preprocess_fields_get($kwp) {
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
    public function preprocess_search_read($p,$kwp){
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
    public function preprocess_create($p,$kwp){
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
    public function preprocess_create_multiple($p,$kwp){
            $p = $this->transformKWParams($p);
            $p = $this->prepareKWParams($p);
      
            //preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $p, $par); // Separación k:v,v,v INTRO k:v,....
            //$p = array_combine($par[1], $par[2]);
            foreach ($p as $clave => $valor) {
                if(!is_array($valor)) {// Si no es una sustitución por una variable múltiple...
                    $p[$clave] = preg_split("/[,]+/x",$valor);
                    if (count($p[$clave]) == 1) { // Si no había array, ...
                        $p[$clave] = $p[$clave][0];
                    }
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
    public function preprocess_write($p,$kwp) {
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
    public function preprocess_unlink($p,$kwp) {
            return array(array(array($p)),NULL);
    }


    /***************POSTPROCESSORS**********************/


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
    public function postprocess_read_array($output) {
        foreach ($output as $key => $value) {
            unset($value["id"]);
            $output[$key] = reset($value);
        }
        return $output;
    }

    

    public function postprocess_read_grid($output){
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
    public function postprocess_search_read_default($output){
        if (count($output[0]) > 1){
            unset($output[0]['id']);
            $aux = array_values($output[0]);
            return $aux[0];
        } else {
            return $output[0]['id'];
        }
    }

    public function postprocess_search_read_integer($output){
        return $this->postprocess_search_read_default($output);
    }

    public function postprocess_search_read_string($output){
        return $this->postprocess_search_read_default($output);
    }
    
    public function postprocess_search_read_float($output){
        return $this->postprocess_search_read_default($output);
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
    public function postprocess_search_read_grid($output){
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
    public function postprocess_search_read_array($output){
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


}
