<?php
/**
 * Este archivo es el callback del step, aquí se procesa el paso en el case (instancia de proceso )
 */
 require_once('/opt/plugins/odooStep/odooStep/dependencies/ripcord/ripcord.php');
try {
  global $Fields;
  $oHeadPublisher = &headPublisher::getSingleton();
    
  //SYS_SYS     //Workspace name
  //PROCESS     //Process UID
  //APPLICATION //Case UID
  //INDEX       //Number delegation
  
  $config = array();
  $config["previousStep"]      = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["PREVIOUS_STEP"];
  $config["previousStepLabel"] = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["PREVIOUS_STEP_LABEL"];
  $config["nextStep"]          = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["NEXT_STEP"];
  $config["nextStepLabel"]     = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["NEXT_STEP_LABEL"];
                                                    
  $oHeadPublisher->addContent("odooStep/stepodooStepApplication"); //Adding a html file .html.
  $oHeadPublisher->addExtJsScript("odooStep/stepodooStepApplication", false); //Adding a javascript file .js
  $oHeadPublisher->assign("CONFIG", $config);

  $osconf = OdooStepConfPeer::retrieveByPK(1); /* Cargamos la configuración básica*/
  $url = $osconf->getUrl();
	$db = $osconf->getDb();
	$username =	$osconf->getUsername();
	$password =	$osconf->getPassword();
  

  //$oCriteria->add(StepPeer::PRO_UID, $_SESSION['PROCESS']);

  //print_r($_GET['UID']);
  //$ostep = OdooStepStepPeer::retrieveByPK($_GET['UID']);
  $c = new Criteria();
  $c->add(OdooStepStepPeer::STEP_ID, $_GET['UID']);

  $ostep = OdooStepStepPeer::doSelectOne($c);
  $parametros = $ostep->getParameters();
  $kwparams = $ostep->getKwParameters();
  $parametros = unserialize($parametros);
  $kwparams = unserialize($kwparams);

  //print_r($Fields);
  //print_r($ostep);
  /*$GLOBALS
	$_SERVER
	$_REQUEST
	$_POST
	$_GET
	$_FILES
	$_ENV
	$_COOKIE
	$_SESSION*/

  // https://secure.php.net/manual/es/function.preg-replace-callback.php
  function varSubtitution($coincidencias) {
    global $Fields;
    return(serialize($Fields["APP_DATA"][$coincidencias[1]])); //Array ( [0] => 8 )
  }
  //print_r("fields:");
  //print_r($Fields);
  //print_r("paramas1:"); //paramas1:Array ( [0] => @@Salida ) 
  //print_r($parametros);
  foreach ($parametros as $key => $value) {
        print_r("Value");
      print_r($value);
    //$aux = NULL;
    $aux = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", "varSubtitution", $value);// Notice: Array to string conversion in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 67
    if ($aux != $value) {$parametros[$key] = unserialize($aux);}
  }

  //print_r("paramas2:");
  //print_r($parametros);

  // https://stackoverflow.com/questions/1987773/use-associative-arrays-with-preg-replace
  $keys = array_keys($kwparams);
  $values = array_values($kwparams);
  $newKeys = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", "varSubtitution", $keys);
  $newValues = preg_replace_callback("/@[@%#\?\x24\=]([A-Za-z_]\w*)/", "varSubtitution", $values);
  $kwparams = array_combine($newKeys, $newValues);

  $fparams = array();
  switch($ostep->getMethod()) {
    case "search":
    case "search_count":
      /*
      $models->execute_kw($db, $uid, $password,'res.partner', 'search',
    array(array(array('is_company', '=', true),array('customer', '=', true))),
    array('offset'=>10, 'limit'=>5));
      $models->execute_kw($db, $uid, $password,'res.partner', 'search_count',
    array(array(array('is_company', '=', true),array('customer', '=', true))));
      */
      while (!empty($parametros)) {
        $aux = array();
        $aux[] =  array_shift ( $parametros );
        $aux[] =  array_shift ( $parametros );
        $aux[] =  array_shift ( $parametros );
        $fparams[] = $aux;
      }
      $fparams = array($fparams);
      break;
    case "read":
      /*
      $models->execute_kw($db, $uid, $password,'res.partner', 'read', 
      array($ids),
      array('fields'=>array('name', 'country_id', 'comment')));
      */
      $fparams = $parametros;
      foreach ($kwparams as $clave => $valor) {
          $kwparams[$clave] = preg_split("/[,]+/x",$valor);
      }
      break;
    case "fields_get":
      /*
      $models->execute_kw($db, $uid, $password,'res.partner', 'fields_get', 
      array(),
      array('attributes' => array('string', 'help', 'type')));
      */
      $fparams = array();
      foreach ($kwparams as $clave => $valor) {
          $kwparams[$clave] = preg_split("/[,]+/x",$valor);
      }
      break;
    case "search_read":
      /*
      $models->execute_kw($db, $uid, $password,'res.partner', 'search_read',
      array(array(array('is_company', '=', true), array('customer', '=', true))),
      array('fields'=>array('name', 'country_id', 'comment'), 'limit'=>5));
      */
      while (!empty($parametros)) {
        $aux = array();
        $aux[] =  array_shift ( $parametros );
        $aux[] =  array_shift ( $parametros );
        $aux[] =  array_shift ( $parametros );
        $fparams[] = $aux;
      }
      $fparams = array($fparams);
      
      foreach ($kwparams as $clave => $valor) {
          $kwparams[$clave] = preg_split("/[,]+/x",$valor);
      }
      break;
    case "create":
      /*
      $id = $models->execute_kw($db, $uid, $password, 'res.partner', 'create',
      array(array('name'=>"New Partner")));
      */
      preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $_POST["newParametros"], $p); // Separación k:v,v,v INTRO k:v,....
      $parametros = array_combine($p[1], $p[2]);
      foreach ($parametros as $clave => $valor) {
          $parametros[$clave] = preg_split("/[,]+/x",$valor);
      }
      $fparams = array(array($parametros));
      break;
    case "write":
      /*
      $models->execute_kw($db, $uid, $password, 'res.partner', 'write',
      array(array($id), array('name'=>"Newer partner")));
      El formato será igual que el KW
      ids:7,5,4,6
      name:Manolo
      */
      preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $_POST["newParametros"], $p); // Separación k:v,v,v INTRO k:v,....
      $parametros = array_combine($p[1], $p[2]);
      $parametros[0] = preg_split("/[,]+/x",$parametros["ids"]);
      $fparams = array($parametros);
      break;
    case "unlink":
      /*
      $models->execute_kw($db, $uid, $password, 'res.partner', 'unlink',
      array(array($id)));
      */
      $fparams = array(array($parametros));
      break;
  }

       
  $common = ripcord::client("$url/xmlrpc/2/common"); /*Fatal error: Class 'ripcord' not found in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 30*/
  //print_r ($common->version());
  //echo "Hello world!<br>";
   /*﻿Array ( 
    [server_serie] => 10.0 
    [server_version_info] => Array ( [0] => 10 [1] => 0 [2] => 0 [3] => final [4] => 0 [5] => ) 
    [server_version] => 10.0 
    [protocol_version] => 1 
    ) Hello world!*/

/*Notice: Undefined variable: http_response_header in /opt/plugins/odooStep/odooStep/dependencies/ripcord/ripcord_client.php on line 485
Could not access http://localhost:8069/xmlrpc/2/common*/

  $uid = $common->authenticate($db, $username, $password, array());
  //print_r ($uid);
  // Acceso al endpoint de objetos y ejecución de una kw
  $models = ripcord::client("$url/xmlrpc/2/object");
  /*$output = $models->execute_kw($db, $uid, $password,
      'res.partner', 'check_access_rights',
      array('read'), array('raise_exception' => false));*/

  /*$models->execute_kw($db, $uid, $password,
    'res.partner', 'search', array(array(array('is_company', '=', true),array('customer', '=', true))));*/

  //print_r("Fparams:");
  //print_r($fparams); 

  $output = $models->execute_kw($db, $uid, $password,$ostep->getModel(),$ostep->getMethod(),$fparams, $kwparams);
  //print_r("OUTPUT:");
  //print_r($output);

  
  // Salvando la salida en la variable indicada.
  $case = new Cases();
  $loaded = $case->loadCase($Fields["APP_UID"]);
  $loaded["APP_DATA"][$ostep->getOutput()] = $output;
  $case->updateCase($Fields["APP_UID"], $loaded);

  


      /*$models->execute_kw($db, $uid, $password,
    'res.partner', 'search', array(
        array(array('is_company', '=', true),
              array('customer', '=', true))));*/

  /*print_r ($output);*//*Output: 1*/

  /*$output = $models->execute_kw($db, $uid, $password,
      'res.partner', 'search', array(
          array(array('is_company', '=', true),
                array('customer', '=', true))));*/
  //print_r ($output); /*Output: Array ( [0] => 8 [1] => 12 [2] => 9 [3] => 45 [4] => 11 [5] => 13 ) */

  
  G::RenderPage("publish", "extJs");
  exit(0);
} catch (Exception $e) {
  echo $e->getMessage();
  exit(0);
}

?>