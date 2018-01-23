<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('simpletest/mock_objects.php');
require_once("SosApp.class.php");
require_once('classes/model/ProcessVariables.php');

# Algunos Mock vienen de los UnitTestCase previos. Dado que todos se ejecutan desde all_tests.php
Mock::generatePartial('BaseSosApp','BaseSosAppTest', array('f','proxy','preprocess_search','postprocess_search_grid'));
Mock::generatePartial('ProcessVariables','ProcessVariablesTest', array('getVarFieldType'));
#Mock::generatePartial('Cases','CasesTest', array('loadCase','updateCase'));

class TestOfExecution extends UnitTestCase {
    public $post = array(), $ostep, $ossc, $scaa;
    public $osc; # OdooStepConfTest object
    public $bsa; # BaseSosAppTest object
    
    /**
     *    Sets up unit test wide variables at the start
     *    of each test method. To be overridden in
     *    actual user test cases.
     *    @access public
     */
    function setUp() {

        $this->osc = new OdooStepConfTest();
        $this->osc->returns('save', true);
        $this->osc->setUrl("http://test");
        $this->osc->setDb("test");
        $this->osc->setUsername("tester");

        $this->test_step = new OdooStepStepTest();
        $this->test_step->returns('save', true);
        $this->test_step->returns('delete', true);

        $this->test_step->setStepId("4553885635943a689c55440011040000");
		$this->test_step->setNombre("PRUEBA");
		$this->test_step->setProUid("test");
		$this->test_step->setModel("res.partner");
		$this->test_step->setMethod("search");
		$this->test_step->setOutput("salida");
        $this->test_step->setParameters('is_company,=,true');
		$this->test_step->setKwParameters("clave:1,2,3,4\nclave2:bar");

        $this->ostep = new OdooStepStepTest();
        $this->ostep->returns('save', true);
        $this->ostep->returns('delete', true);

        $this->ostep->setStepId("4553885635943a689c55440011040001");
		$this->ostep->setNombre("PRUEBA2");
		$this->ostep->setProUid("test2");
		$this->ostep->setModel("product.product");
		$this->ostep->setMethod("search");
		$this->ostep->setOutput("output");
        $this->ostep->setParameters('is_company,=,false');
		$this->ostep->setKwParameters("clave:3,3,4,5\nclave2:foo");

        #$cases = new CasesTest();
        #$cases->returns('loadCase',array('APP_DATA'=>array('output'=>array())),'*');
        #$cases->returns('updateCase',null,'*');
        
        $pv = new ProcessVariablesTest();
        $pv->returns('getVarFieldType','grid');

        $this->bsa = new BaseSosAppTest();
       
        // f function (factory) returns $this->ostep when receive 'OdooStepStep' as parameter.
        $this->bsa->returns('f', $this->ostep, array('OdooStepStep'));
        #$this->bsa->returns('f', $cases, array('Cases'));
        $this->bsa->returns('proxy', $pv,  array('ProcessVariablesPeer','doSelectOne','*'));
        $this->bsa->returns('preprocess_search', array("params","kwparams"));
        $this->bsa->returns('postprocess_search_grid', 'salida_procesada');
        $this->bsa->returns('proxy', $this->test_step,  array('OdooStepStepPeer','doSelectOne','*'));
        $this->bsa->returns('proxy', $this->osc,  array('OdooStepConfPeer','retrieveByPK','*'));
    }

    /**
     *    Clears the data set in the setUp() method call.
     *    To be overridden by the user in actual user test cases.
     *    @access public
     */
    function tearDown() {
    }

    /**
     * You can place some test case set up into the constructor 
     * to be run once for all the methods in the test case, 
     * but you risk test interference that way.
     */
    function __construct() {
        parent::__construct('CRUD de Odoo Steps');
    }
    /* Estos métodos son muy difíciles de probar.
        public function execute($uid)
        public function saveOutput($output)
        public function xmlCall()
        public function xmlCallMultiple()
    */

    function testLoadOdooStep() {
        $this->bsa->loadOdooStep(13); # Random number
        $this->assertEqual($this->test_step,$this->bsa->ostep);
    }

    function testLoadConfig() {
        $this->bsa->loadConfig();
        $this->assertEqual($this->osc->getUrl(),$this->bsa->url);
        $this->assertEqual($this->osc->getDb(),$this->bsa->db);
        $this->assertEqual($this->osc->getUsername(),$this->bsa->username);
        $this->assertEqual($this->osc->getPassword(),$this->bsa->password);
    }

    function testTransformParams() {
        $s = $this->bsa->TransformParams("is_company,=,true");
        $this->assertEqual($s,array('is_company','=','true'));
    }

    function testTransformKWParams() {
        $s = $this->bsa->TransformKWParams("clave:1,2,3,4\nclave2:bar");
        $this->assertEqual($s,array('clave'=>'1,2,3,4','clave2'=>'bar'));
    }

    function testPreprocessMethod() {
        $this->bsa->loadOdooStep(13); # Random number
        $this->bsa->preprocessMethod();
        $this->assertEqual($this->bsa->params,"params");
        $this->assertEqual($this->bsa->kwparams,"kwparams");
    }

    function testGrid_field_to_array() {
        $grid = array(array('field1'=>1,'field2'=>2,'field3'=>3),
                    array('field1'=>4,'field2'=>5,'field3'=>6),
                    array('field1'=>7,'field2'=>8,'field3'=>9));
        $field = "field3";
        $array = $this->bsa->grid_field_to_array($grid,$field);
        $this->assertEqual($array,array(3,6,9));
    }

    function testVarSubstitution() {
        global $Fields;

        $Fields["APP_DATA"]['foo595'] = 'foo';

        $coincidencias = array('@@','foo595');

        $serial = $this->bsa->varSubtitution($coincidencias);
        $this->assertEqual($serial,serialize(array('foo595',null,'foo')));
    }

    function testVarSubstitution2() {
        global $Fields;

        $Fields["APP_DATA"]['foo595'] = array(array('field1'=>1,'field2'=>2,'field3'=>3),
                    array('field1'=>4,'field2'=>5,'field3'=>6),
                    array('field1'=>7,'field2'=>8,'field3'=>9));

        $coincidencias = array('@@','foo595','field3');

        $serial = $this->bsa->varSubtitution($coincidencias);
        $this->assertEqual($serial,serialize(array('foo595','field3',array(3,6,9))));
    }

    function testPrepareParams() {
        global $Fields;
        $Fields["APP_DATA"]['foo595'] = array(array('field1'=>1,'field2'=>2,'field3'=>3),
                    array('field1'=>4,'field2'=>5,'field3'=>6),
                    array('field1'=>7,'field2'=>8,'field3'=>9));
        $Fields["APP_DATA"]['bar595'] = 'bar';

        $p = array('juan','benito','@@foo595[field3]','@@bar595');

        $p = $this->bsa->prepareParams($p);
        $this->assertEqual($p,array('juan','benito',array(3,6,9),'bar'));
    }

    function testPrepareKWParams() {
        global $Fields;
        $Fields["APP_DATA"]['foo595'] = array(array('field1'=>1,'field2'=>2,'field3'=>3),
                    array('field1'=>4,'field2'=>5,'field3'=>6),
                    array('field1'=>7,'field2'=>8,'field3'=>9));
        $Fields["APP_DATA"]['bar595'] = 'bar';

        $kwp = array('j'=>'juan','b'=>'benito','ids'=>'@@foo595[field3]','f' =>'@@bar595');
        $kwp = $this->bsa->prepareKWParams($kwp);
        $this->assertEqual($kwp,array('j'=>'juan','b'=>'benito','ids'=>array(3,6,9),'f'=>'bar'));
        
        $this->assertEqual($this->bsa->mvar['pkey'][0],'ids');
        $ref = &$kwp['ids'];
        $this->assertEqual($this->bsa->mvar['ref'][0],$ref);
        $this->assertEqual($this->bsa->mvar['value'][0],array(3,6,9));
        $this->assertEqual($this->bsa->mvar['name'][0],'foo595');
        $this->assertEqual($this->bsa->mvar['field'][0],'field3');
    }

    function testPostprocessMethod() {
        $this->bsa->loadOdooStep(13); # Random number
        
        $this->bsa->postprocessMethod();
        $this->assertEqual($this->bsa->output,"salida_procesada");
    }

    /* # No se puede probar 
    function testSaveOutput() {

        $this->bsa->outputvar = $this->bsa->proxy('ProcessVariablesPeer','doSelectOne',13); # random number
        $this->bsa->saveOutput($output);
    }*/

}