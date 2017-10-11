<?php
        /*set_include_path(realpath(dirname(__FILE__) . "/../odooStep"));
        $rootDir = realpath(__DIR__."/../../../../");

         //define("PATH_SEP", DIRECTORY_SEPARATOR);


        define("PATH_HOME", $rootDir . PATH_SEP . "workflow/");
        define("PATH_CORE", PATH_HOME . "engine/");

        define("PATH_PLUGIN_OS",PATH_CORE . "/plugins/odooStep/");
        set_include_path(
            PATH_PLUGIN_OS.PATH_SEPARATOR.
            get_include_path()
        );    

        require_once("cp_appAjax.php");*/
        //
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('../classes/log.php');

class TestOfLogging extends UnitTestCase {
    function testFirstLogMessagesCreatesFileIfNonexistent() {
                @unlink(dirname(__FILE__) . '/../temp/test.log');
        $log = new Log(dirname(__FILE__) . '/../temp/test.log');
        $log->message('Should write this to a file');
        $this->assertTrue(file_exists(dirname(__FILE__) . '/../temp/test.log'));
    }
}