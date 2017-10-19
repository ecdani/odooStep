<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('simpletest/mock_objects.php');
require_once("ScApp.class.php");
Mock::generatePartial('OdooStepStep','OdooStepStepTest',array('save','delete'));
Mock::generatePartial('scApp','scAppTest', array('f','osspProxy'));

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
        //$this->scaa->returns('osspProxy', array($this->ostep),array('*'));
        $this->scaa->returns('osspProxy', $this->ostep ,array('retrieveByPK','*'));
        $this->scaa->returns('osspProxy', array($this->ostep),array('doSelect','*'));
        $this->scaa->returns('osspProxy', $this->ostep,array('doSelectOne','*'));
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
    /*
    public function revertParams($params)
    public function revertKWParams($kwparams)
    public function transformParams($params)
    public function transformKWParams($kwparams)
    public function getStep($r, $i, $textFilter)
    public function saveStep($post)
    public function listSteps($post)
    public function deleteStep($post)
    public function nextStepID()
    public function createStep($post)
    */

    /*function testRevertParams() {
        //$s = $this->scaa->revertParams('a:3:{i:0;s:10:"is_company";i:1;s:1:"=";i:2;s:4:"true";}');
        $s = $this->scaa->revertParams($this->ostep->getParameters());
        $this->assertEqual($s,"is_company,=,true");
    }

    function testRevertKWParams() {
        $s = $this->scaa->revertKWParams($this->ostep->getKWParameters());
        $this->assertEqual($s,"clave:1,2,3,4\nclave2:bar");
    }

    function testTransformParams() {
        $s = $this->scaa->TransformParams("is_company,=,true");
        $this->assertEqual($s,$this->ostep->getParameters());
    }

    function testTransformKWParams() {
        $s = $this->scaa->TransformKWParams("clave:1,2,3,4\nclave2:bar");
        $this->assertEqual($s,$this->ostep->getKWParameters());
    }*/

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

    function testCreateStep() {

    }
}