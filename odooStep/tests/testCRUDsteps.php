<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('simpletest/mock_objects.php');
require_once("ScApp.class.php");
Mock::generatePartial('OdooStepStep','OdooStepStepTest',array('save','delete'));
Mock::generatePartial('scApp','scAppTest', array('f','proxy'));

class TestOfCRUDSteps extends UnitTestCase {
    public $post = array(), $ostep, $ossc, $scaa;
    
    /**
     *    Sets up unit test wide variables at the start
     *    of each test method. To be overridden in
     *    actual user test cases.
     *    @access public
     */
    function setUp() {
        $this->ostep = new OdooStepStepTest();
        $this->ostep->returns('save', true);
        $this->ostep->returns('delete', true);

        $this->ostep->setStepId("4553885635943a689c55440011040000");
		$this->ostep->setNombre("PRUEBA");
		$this->ostep->setProUid("test");
		$this->ostep->setModel("res.partner");
		$this->ostep->setMethod("search");
		$this->ostep->setOutput("salida");
        $this->ostep->setParameters('is_company,=,true');
		$this->ostep->setKwParameters("clave:1,2,3,4\nclave2:bar");
        

        $this->scaa = new scAppTest();
        //$this->scaa->setReturnReference('newOdooStepConf', $this->osc);
        
        $this->scaa->returns('f', $this->ostep,array('OdooStepStep'));
        //$this->scaa->returns('proxy', array($this->ostep),array('*'));
        $this->scaa->returns('proxy', $this->ostep ,array('OdooStepStepPeer','retrieveByPK','*'));
        $this->scaa->returns('proxy', array($this->ostep),array('OdooStepStepPeer','doSelect','*'));
        $this->scaa->returns('proxy', $this->ostep,array('OdooStepStepPeer','doSelectOne','*'));
    }

    /**
     *    Clears the data set in the setUp() method call.
     *    To be overridden by the user in actual user test cases.
     *    @access public
     */
    function tearDown() {
    }

    function __construct() {
        parent::__construct('CRUD de Odoo Steps');
    }

    function testGetStep() {
        //$limit, $start, $textFilter
        $r = 1;
        $i = 0;
        $textFilter = NULL;
        $s = $this->scaa->getStep($r, $i, $textFilter);
        $c = array(1,array(array(
				"ID" => NULL ,
				"PRO_UID" => "test" ,
				"NOMBRE" => "PRUEBA" ,
				"MODEL" => "res.partner" ,
				"METHOD" => "search" ,
				"PARAMETERS" => 'is_company,=,true',
				"KW_PARAMETERS" => "clave:1,2,3,4\nclave2:bar", // Las "" aceptan el \n las '' no.
				"OUTPUT" => 'salida'
			)));
        $this->assertEqual($s,$c);        
    }

    function testSaveStep() {
        $post = array();
        $post["newNombre"] = "PRUEBA" ;
		$post["newProceso"] = "test";
		$post["newModelo"] = "res.partner";
		$post["newMetodo"] = "search";
		$post["newSalida"] = 'salida';
		$post["newParametros"] = 'is_company,=,true';
		$post["newParametrosKW"] = "clave:1,2,3,4\nclave2:bar";
        $post["id"] = "4553885635943a689c55440011040000";

        $s = $this->scaa->saveStep($post);
        $this->assertTrue($s["success"]);
        $this->assertEqual($s["respuesta"]->getNombre(),"PRUEBA");
        $this->assertEqual($s["respuesta"]->getStepId(),"4553885635943a689c55440011040000");
    }

    function testListSteps() {
        $post = array();
        $post["pageSize"] = 10;
		$post["limit"] = 1;
		$post["start"] = 0;
        $s = $this->scaa->listSteps($post);
        $this->assertTrue($s["success"]);
        $this->assertEqual($s["resultTotal"],1);
        $this->assertEqual($s["resultRoot"][0]["NOMBRE"],"PRUEBA");
    }

    function testDeleteStep() {
        $post = array();
        $post["id"] = "4553885635943a689c55440011040000";

        $s = $this->scaa->deleteStep($post);
        $this->assertTrue($s["success"]);
    }

    function testNextStepID() {
        $s = $this->scaa->nextStepID();
        $this->assertEqual($s,"4553885635943a689c55440011040001");
    }

    # Hay un singleton.
    function testCreateStep() {

    }
}