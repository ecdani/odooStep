<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('simpletest/mock_objects.php');
require_once("cp_appAjax.php");
Mock::generate('OdooStepConf');
Mock::generatePartial('cpAppAjax','cpAppAjaxTest', array('newOdooStepConf'));

class TestOfCPSave extends UnitTestCase {
    public $post = array(), $osc, $cpaa;
    
    /**
     *    Sets up unit test wide variables at the start
     *    of each test method. To be overridden in
     *    actual user test cases.
     *    @access public
     */
    function setUp() {
        $this->osc = new MockOdooStepConf();
        $this->osc->returns('save', true);

        $this->cpaa = new cpAppAjaxTest();
        $this->cpaa->setReturnReference('newOdooStepConf', $this->osc);
    }

    /**
     *    Clears the data set in the setUp() method call.
     *    To be overridden by the user in actual user test cases.
     *    @access public
     */
    function tearDown() {
    }

    function __construct() {
        parent::__construct('Guardado de configuracion');
    }

    function testSave() {
        $post["txtUrl"] = "url2";
		$post["txtDb"] = "db";
		$post["txtUsuario"] = "usuario";
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertTrue($s->success);
    }

    function testMalformed1() {
		$post["txtDb"] = "db";
		$post["txtUsuario"] = "usuario";
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testMalformed2() {
		$post["txtUsuario"] = "usuario";
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testMalformed3() {
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testMalformed4() {
        $post = null;
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }
}