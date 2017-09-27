<?php

  // include base peer class
  require_once 'classes/model/om/BaseOdooStepConfPeer.php';

  // include object class
  include_once 'classes/model/OdooStepConf.php';
  //Propel::init( PATH_CORE . "config/databases.php" );


/**
 * Skeleton subclass for performing query and update operations on the 'ODOOSTEP_CONFIG' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    classes.model
 */
class OdooStepConfPeer extends BaseOdooStepConfPeer {
    public function ejecucion() {
      try{
        //$con = Propel::getConnection('workflow');
        Propel::init( PATH_CORE . "config/databases.php" );
        $con = Propel::getConnection(OdooStepConfPeer::DATABASE_NAME);

        //$con->begin();
        $sql = 'SELECT * FROM ODOOSTEP_CONFIG';  
        $stmt = $con->createStatement();
            $rs = $stmt->executeQuery($sql, ResultSet::FETCHMODE_NUM); 
            $osconfs = OdooStepConfPeer::populateObjects($rs);    
        //$con->commit();

        

        
        //echo $rs;
        return $osconfs;
       } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

} // OdooStepConfPeer
