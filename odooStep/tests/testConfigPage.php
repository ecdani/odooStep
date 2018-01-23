<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('simpletest/mock_objects.php');
require_once("CpApp.class.php");
Mock::generatePartial('OdooStepConf','OdooStepConfTest',array('save'));
Mock::generatePartial('cpApp','cpAppTest', array('f','proxy'));

class TestOfCPSave extends UnitTestCase {
    public $post = array(), $osc, $oscp, $cpaa;
    
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

        $this->cpaa = new cpAppTest();
        //$this->cpaa->setReturnReference('newOdooStepConf', $this->osc);
        //$this->cpaa->returns('f', $this->osc);
        $this->cpaa->returns('f', $this->osc,array('OdooStepConf'));
        $this->cpaa->returns('proxy', $this->osc ,array('OdooStepConfPeer','retrieveByPK','*'));
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
        $post["txtUrl"] = "url";
		$post["txtDb"] = "db";
		$post["txtUsuario"] = "usuario";
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post)); // El retrieve debe hacerse como en CRUD
        $this->assertTrue($s->success);
    }

    function testSaveMalformed1() {
		$post["txtDb"] = "db";
		$post["txtUsuario"] = "usuario";
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testSaveMalformed2() {
		$post["txtUsuario"] = "usuario";
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testSaveMalformed3() {
		$post["txtPassword"] = "password";
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testSaveMalformed4() {
        $post = null;
        $s = json_decode($this->cpaa->saveConf($post));
        $this->assertFalse($s->success);
    }

    function testLoad() {

        $s = $this->cpaa->loadConf();
        $this->assertIsA($s, 'Array');
        $this->assertTrue(array_key_exists('Username', $s));
        $this->assertTrue(array_key_exists('Url', $s));
        $this->assertTrue(array_key_exists('Db', $s));
        if (!is_null($s['Username'])) {
            
            $this->assertTrue(is_string($s['Username']));
            $this->assertTrue(is_string($s['Url']));
            $this->assertTrue(is_string($s['Db']));
        }
    }



}