<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('simpletest/mock_objects.php');
require_once("ScApp.class.php");
Mock::generatePartial('OdooStepStep','OdooStepStepTest',array('save'));
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

        $this->ostep->setStepId("4553885635943a689c55440011040000");
		$this->ostep->setNombre("PRUEBA");
		$this->ostep->setProUid("test");
		$this->ostep->setModel("res.partner");
		$this->ostep->setMethod("search");
		$this->ostep->setOutput("salida");		
        $this->ostep->setParameters('a:3:{i:0;s:10:"is_company";i:1;s:1:"=";i:2;s:4:"true";}');
		$this->ostep->setKwParameters('a:0:{}');

        $this->scaa = new scAppTest();
        //$this->scaa->setReturnReference('newOdooStepConf', $this->osc);
        //$this->scaa->returns('f', $this->osc);
        $proxy = function($f,$p) {
            switch($f){
                case "retrieveByPK":
                    return $this->ostep;
                break;
                case "doSelect":
                    return  array($this->ostep);
                break;
                case "doSelectOne":
                    return $this->ostep;
                break;
            }
        };

        $this->scaa->returns('f', $this->ostep,array('OdooStepConf'));
        $this->scaa->returns('osspProxy', $proxy);
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

    function testRevertParams() {
        //$s = $this->scaa->revertParams('a:3:{i:0;s:10:"is_company";i:1;s:1:"=";i:2;s:4:"true";}');
        $s = $this->scaa->revertParams($this->ostep->getParameters());
        $this->assertEqual($s,"is_company,=,true");
    }

    function testRevertKWParams() {

    }

    function testTransformParams() {

    }

    function testTransformKWParams() {

    }

    function testGetStep() {

    }

    function testSaveStep() {

    }

    function testListSteps() {

    }

    function testDeleteStep() {

    }

    function testNextStepID() {

    }

    function testCreateStep() {

    }
}