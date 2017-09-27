<?php
//require_once ("classes/model/class.case.php");
G::LoadClass("case");


$url = "http://localhost:8069";
$db = "odoo";
$username = "dani";
$password = "postgres";

//$uid = Cases::loadCase();

//workflow/engine/classes/class.case.php.

//echo $uid;

echo G::json_encode( $_POST);
$APP_UID = $_SESSION['APPLICATION'];
//$_POST

//require_once('dependencies/ripcord/ripcord.php');
//$info = ripcord::client('https://demo.odoo.com/start')->start();
//list($url, $db, $username, $password) =
//  array($info['host'], $info['database'], $info['user'], $info['password']);

//$common = ripcord::client("$url/xmlrpc/2/common");
//$common->version();

//$case = Cases::loadCase(G.getAppUid())


try {
  //SYS_SYS     //Workspace name
  //PROCESS     //Process UID
  //APPLICATION //Case UID
  //INDEX       //Number delegation
  
  /*$oApp    = new Cases();
  $aFields = $oApp->loadCase($_SESSION["APPLICATION"]);
  $aData   = $aFields["APP_DATA"];
  
  $aResult = array();

  foreach ($aData as $index => $value) {
    $aResult[] = array("VARIABLE" => $index, "VALUE" => $value);
  }*/

  /// VAMOS A PONER POR AQUI EL XML-RPC
  /*header('Content-Type: text/plain');
$rpc = "http://10.0.0.10/api.php";
$client = new xmlrpc_client($rpc, true);
$resp = $client->call('methodname', array());
print_r($resp);*/
  
  //echo "{success: " . true . ", resultTotal: " . count($aResult) . ", resultRoot: " . G::json_encode($aResult) . "}";
  //echo G::json_encode(array("success" => true, "resultTotal" => count($aResult), "resultRoot" => $aResult)); // Esto será la vuelta
} catch (Exception $e) {
  echo null;
}
?>