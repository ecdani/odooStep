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


  $url = "http://localhost:8069";
  $db = "odoo";
  $username = "ec.dani@gmail.com";
  $password = "a";

  $common = ripcord::client("$url/xmlrpc/2/common"); /*Fatal error: Class 'ripcord' not found in /opt/plugins/odooStep/odooStep/stepodooStepApplication.php on line 30*/
  print_r ($common->version());
  echo "Hello world!<br>";
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
  $output = $models->execute_kw($db, $uid, $password,
      'res.partner', 'check_access_rights',
      array('read'), array('raise_exception' => false));
  print_r ($output);/*Output: 1*/

  $output = $models->execute_kw($db, $uid, $password,
      'res.partner', 'search', array(
          array(array('is_company', '=', true),
                array('customer', '=', true))));
  print_r ($output); /*Output: Array ( [0] => 8 [1] => 12 [2] => 9 [3] => 45 [4] => 11 [5] => 13 ) */

  
  G::RenderPage("publish", "extJs");
  exit(0);
} catch (Exception $e) {
  echo $e->getMessage();
  exit(0);
}

?>