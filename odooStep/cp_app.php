<?php
/**
 * PHP code to be executed on the server side to publish the page.
 */
include_once ("classes/model/OdooStepConf.php"); // Incluimos las clases de propel
include_once ("classes/model/OdooStepConfPeer.php");
Propel::init( PATH_CORE . "config/databases.php" );

require_once("cp_appAjax.php");

try {
    $oHeadPublisher = &headPublisher::getSingleton(); /* Render page */
    
    $G_MAIN_MENU        = "processmaker";
    $G_ID_MENU_SELECTED = "ID_ODOOSTEP_MNU_01";
    //$G_SUB_MENU             = "setup";
    //$G_ID_SUB_MENU_SELECTED = "ID_FULLPLUGIN_02";

    $config = array();
    $o = new cpApp();
    $config["resultado"] = $o->loadConf();

    // A partir de aquí es EXTJS quien construye la página, en cp_app.js
    $oHeadPublisher->addContent("odooStep/cp_app"); //Adding a html file .html
    $oHeadPublisher->addExtJsScript("odooStep/cp_app", false); //Adding a javascript file .js
    $oHeadPublisher->assign("CONFIG", $config);

    G::RenderPage("publish", "extJs");
} catch (Exception $e) {
    $G_PUBLISH = new Publisher;
    
    $aMessage["MESSAGE"] = $e->getMessage();
    $G_PUBLISH->AddContent("xmlform", "xmlform", "odooStep/messageShow", "", $aMessage);
    G::RenderPage("publish", "blank");
}
?>