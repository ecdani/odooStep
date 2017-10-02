<?php
function userGet($r, $i)
{  $userName = array("John", "Amy", "Dan", "Elizabeth", "Mike", "Wil", "Ernest", "Albert", "Sue", "Freddy",
                     "Mary", "Tom", "Paul", "Amber", "Bibi", "Boris", "Cameron", "Cesar", "Carmen", "Ben",
                     "Amadeo", "Angela", "Betty", "Benny", "Brenda", "Christian", "Celia", "Franklin", "Fiona", "Felix",
                     "Amelia", "Chelsea", "David", "Donna", "Edison", "Erika", "Ginger", "Gilbert", "Heidi", "Hans",
                     "Andy", "Bruce", "Corinna", "Evan", "Austin", "Flavio", "Gaby", "Gally", "Harold", "Isabella");

   $user = array();

   for ($ii = 0; $ii <= 50 - 1; $ii++) {
     $user[] = array("ID" => $ii + 10, "NAME" => $userName[$ii], "AGE" => rand(20, 40), "BALANCE" => rand(100, 255));
   }

   return (array(count($user), array_slice($user, $i, $r)));
}

try {
  $option = $_POST["option"];

  switch ($option) {
    case "LST": $pageSize = $_POST["pageSize"];

                $limit = isset($_POST["limit"])? $_POST["limit"] : $pageSize;
                $start = isset($_POST["start"])? $_POST["start"] : 0;

                list($userNum, $user) = userGet($limit, $start);

                //echo "{success: " . true . ", resultTotal: " . count($user) . ", resultRoot: " . G::json_encode($user) . "}";
                echo G::json_encode(array("success" => true, "resultTotal" => $userNum, "resultRoot" => $user));
                break;
    case "NEWSTEP" :
    try {
            $ostep = new OdooStepStep();

            $c = new Criteria();
            $c->addDescendingOrderByColumn('ID');
            $lastStep = OdooStepStepPeer::doSelectOne($c);
            if (! is_null( $lastStep )) {
                $id = $lastStep->getId();
                $stepid = $lastStep->getStepId();
                $contador = substr($stepid, -4);
                $int = (int)$contador;
                $int ++;
                $str = (string)$int;
                $strlen = strlen($str);
                $stepid = substr_replace($stepid, $str, -$strlen);
            } else {
                $stepid = "4553885635943a689c55440011040000";
            }

            $ostep->setStepId($stepid);
            $ostep->setProUid($_POST["newProceso"]);
            $ostep->setModel($_POST["newModelo"]);
            $ostep->setMethod($_POST["newMetodo"]);

            $parametros = preg_split("/[\s,]+/",$_POST["newParametros"]);// Separación v,v,v,...
            $fparams = array();

            preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $_POST["newParametrosKW"], $p); // Separación k:v,v,v INTRO k:v,....
            $kwparams = array_combine($p[1], $p[2]);

            switch($_POST["newMetodo"]) {
              case "search"||"search_count":
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
                $fparams = array($parametros);
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
            
                       
            $ostep->setParameters(serialize($fparams));

           
            $ostep->setKwParameters(serialize($kwparams));
            $ostep->save();
            //PMPluginRegistry::registerStep( "odooStep", $ostep->getStepId(), "stepodooStepApplication",$ostep->getModel());
            
            //PMPlugin::registerStep($ostep->getStepId(), "stepodooStepApplication",$ostep->getModel());
            $oPluginRegistry = PMPluginRegistry::getSingleton();
            $oPluginRegistry->registerStep( "odooStep", $ostep->getStepId(), "stepodooStepApplication",$ostep->getModel());
            $oPluginRegistry->save();
          }

          catch(Exception $e) {
            throw $e;
            echo G::json_encode(array(
            "excepcion" => $e));
          }

          echo G::json_encode(array(
            "success" => true,
            "salvando" => true,
            "respuesta" => print_r($_SESSION)
            //"registro" => print_r($oPluginRegistry),
          ));
    break;
  }
  
} catch (Exception $e) {
  echo null;
}
?>