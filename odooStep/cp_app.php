<?php
/**
 * PHP code to be executed on the server side to publish the page.
 */
include_once ("classes/model/OdooStepConf.php"); // Incluimos las clases de propel
include_once ("classes/model/OdooStepConfPeer.php");
Propel::init( PATH_CORE . "config/databases.php" );

try {
  $oHeadPublisher = &headPublisher::getSingleton(); /* Render page */
  
  $G_MAIN_MENU        = "processmaker";
  $G_ID_MENU_SELECTED = "ID_ODOOSTEP_MNU_01";
  //$G_SUB_MENU             = "setup";
  //$G_ID_SUB_MENU_SELECTED = "ID_FULLPLUGIN_02";

  $config = array();
  //$config["pageSize"] = 15;
  //$config["message"] = "Hello world!";

  try{
    $con = Propel::getConnection(OdooStepConfPeer::DATABASE_NAME);
    $OdooStepConf = OdooStepConfPeer::retrieveByPK(1);
    if (! is_null ( $OdooStepConf ) ) {
      $array = $OdooStepConf->toArray();
    } else {
      $array = array();
      $array["Url"] = NULL;
      $array["Db"] = NULL;
      $array["Username"] = NULL;
    }
    $config["resultado"] = $array;
  } catch (PropelException $e) {
            $con->rollback();
            throw $e;
  }
    
  // A partir de aquí es EXTJS quien construye la página, en cp_app.js
  $oHeadPublisher->addContent("odooStep/cp_app"); //Adding a html file .html
  $oHeadPublisher->addExtJsScript("odooStep/cp_app", false); //Adding a javascript file .js
  $oHeadPublisher->assign("CONFIG", $config);

  G::RenderPage("publish", "extJs");
  //include_once ("tests/testConfigPage.php");
} catch (Exception $e) {
  $G_PUBLISH = new Publisher;
  
  $aMessage["MESSAGE"] = $e->getMessage();
  $G_PUBLISH->AddContent("xmlform", "xmlform", "odooStep/messageShow", "", $aMessage);
  G::RenderPage("publish", "blank");
}
?>