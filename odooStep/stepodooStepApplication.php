<?php
/**
 * Este archivo es el callback del step, aquí se procesa el paso en el case (instancia de proceso )
 */
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

  $common = ripcord::client("$url/xmlrpc/2/common");
  print_r ($common->version());
  echo "Hello world!<br>";


  $uid = $common->authenticate($db, $username, $password, array());
  //print_r ($uid);
  // Acceso al endpoint de objetos y ejecución de una kw
  $models = ripcord::client("$url/xmlrpc/2/object");
  $output = $models->execute_kw($db, $uid, $password,
      'res.partner', 'check_access_rights',
      array('read'), array('raise_exception' => false));
  print_r ($output);
  $output = $models->execute_kw($db, $uid, $password,
      'res.partner', 'search', array(
          array(array('is_company', '=', true),
                array('customer', '=', true))));
  print_r ($output);

  
  G::RenderPage("publish", "extJs");
  exit(0);
} catch (Exception $e) {
  echo $e->getMessage();
  exit(0);
}

?>