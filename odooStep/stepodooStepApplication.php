<?php
/**
 * This file is the callback of the step, here the step is processed in the case (process instance)
 */
require_once('SosApp.class.php');

try {
    global $Fields;
    $oHeadPublisher = &headPublisher::getSingleton();
      
    //SYS_SYS     //Workspace name
    //PROCESS     //Process UID
    //APPLICATION //Case UID
    //INDEX       //Number delegation
    
    $config = array();
    if (isset($Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["PREVIOUS_STEP"]) ){
      $config["previousStep"]      = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["PREVIOUS_STEP"];
      $config["previousStepLabel"] = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["PREVIOUS_STEP_LABEL"];
    }
    $config["nextStep"]          = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["NEXT_STEP"];
    $config["nextStepLabel"]     = $Fields["APP_DATA"]["__DYNAFORM_OPTIONS"]["NEXT_STEP_LABEL"];
                                                      
    $oHeadPublisher->addContent("odooStep/stepodooStepApplication"); //Adding a html file .html.
    $oHeadPublisher->addExtJsScript("odooStep/stepodooStepApplication", false); //Adding a javascript file .js
    $oHeadPublisher->assign("CONFIG", $config);

    $sosapp = new SosApp();
    $sosapp->execute($_GET['UID']);
   
    G::RenderPage("publish", "extJs");
    exit(0);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(0);
}

?>