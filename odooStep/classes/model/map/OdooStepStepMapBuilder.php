<?php

require_once 'propel/map/MapBuilder.php';
include_once 'creole/CreoleTypes.php';


/**
 * This class adds structure of 'ODOOSTEP_STEP' table to 'workflow' DatabaseMap object.
 *
 *
 *
 * These statically-built map classes are used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    workflow.classes.model.map
 */
class OdooStepStepMapBuilder
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'classes.model.map.OdooStepStepMapBuilder';

    /**
     * The database map.
     */
    private $dbMap;

    /**
     * Tells us if this DatabaseMapBuilder is built so that we
     * don't have to re-build it every time.
     *
     * @return     boolean true if this DatabaseMapBuilder is built, false otherwise.
     */
    public function isBuilt()
    {
        return ($this->dbMap !== null);
    }

    /**
     * Gets the databasemap this map builder built.
     *
     * @return     the databasemap
     */
    public function getDatabaseMap()
    {
        return $this->dbMap;
    }

    /**
     * The doBuild() method builds the DatabaseMap
     *
     * @return     void
     * @throws     PropelException
     */
    public function doBuild()
    {
        $this->dbMap = Propel::getDatabaseMap('workflow');

        $tMap = $this->dbMap->addTable('ODOOSTEP_STEP');
        $tMap->setPhpName('OdooStepStep');

        $tMap->setUseIdGenerator(true);

        $tMap->addPrimaryKey('ID', 'Id', 'int', CreoleTypes::INTEGER, true, null);

        $tMap->addColumn('STEP_ID', 'StepId', 'string', CreoleTypes::VARCHAR, true, 255);

        $tMap->addColumn('PRO_UID', 'ProUid', 'string', CreoleTypes::VARCHAR, false, 32);

        $tMap->addColumn('NOMBRE', 'Nombre', 'string', CreoleTypes::VARCHAR, true, 128);

        $tMap->addColumn('MODEL', 'Model', 'string', CreoleTypes::VARCHAR, true, 128);

        $tMap->addColumn('METHOD', 'Method', 'string', CreoleTypes::VARCHAR, true, 128);

        $tMap->addColumn('PARAMETERS', 'Parameters', 'string', CreoleTypes::LONGVARCHAR, false, null);

        $tMap->addColumn('KW_PARAMETERS', 'KwParameters', 'string', CreoleTypes::LONGVARCHAR, false, null);

    } // doBuild()

} // OdooStepStepMapBuilder
