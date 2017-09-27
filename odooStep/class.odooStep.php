<?php
/**
 * class.odooStep.php
 *  
 */

  class odooStepClass extends PMPlugin {
    function __construct() { // Tal vez aquí se pueda establecer mas include paths
      set_include_path(
        PATH_PLUGINS . 'odooStep' . PATH_SEPARATOR .
        get_include_path()
      );
    }

    function setup()
    {
    }

    function getFieldsForPageSetup()
    {
    }

    function updateFieldsForPageSetup()
    {
    }

  }
?>