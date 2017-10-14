<?php

require_once(dirname(__FILE__) . '/simpletest/autorun.php');

class AllTests extends TestSuite
{
    public function __construct()
    {
        parent::__construct('All tests for Odoo Step ');
        $this->addFile(dirname(__FILE__) . '/testConfigPage.php');
        $this->addFile(dirname(__FILE__) . '/testCRUDsteps.php');
        //$this->addFile(dirname(__FILE__) . '/testInstall.php');
    }
}
