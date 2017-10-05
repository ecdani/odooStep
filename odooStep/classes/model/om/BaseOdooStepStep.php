<?php

require_once 'propel/om/BaseObject.php';

require_once 'propel/om/Persistent.php';


include_once 'propel/util/Criteria.php';

include_once 'classes/model/OdooStepStepPeer.php';

/**
 * Base class that represents a row from the 'ODOOSTEP_STEP' table.
 *
 * 
 *
 * @package    workflow.classes.model.om
 */
abstract class BaseOdooStepStep extends BaseObject implements Persistent
{

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        OdooStepStepPeer
    */
    protected static $peer;

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the step_id field.
     * @var        string
     */
    protected $step_id;

    /**
     * The value for the pro_uid field.
     * @var        string
     */
    protected $pro_uid = '';

    /**
     * The value for the nombre field.
     * @var        string
     */
    protected $nombre;

    /**
     * The value for the model field.
     * @var        string
     */
    protected $model;

    /**
     * The value for the method field.
     * @var        string
     */
    protected $method;

    /**
     * The value for the parameters field.
     * @var        string
     */
    protected $parameters;

    /**
     * The value for the kw_parameters field.
     * @var        string
     */
    protected $kw_parameters;

    /**
     * The value for the output field.
     * @var        string
     */
    protected $output;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Get the [id] column value.
     * 
     * @return     int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [step_id] column value.
     * 
     * @return     string
     */
    public function getStepId()
    {

        return $this->step_id;
    }

    /**
     * Get the [pro_uid] column value.
     * 
     * @return     string
     */
    public function getProUid()
    {

        return $this->pro_uid;
    }

    /**
     * Get the [nombre] column value.
     * 
     * @return     string
     */
    public function getNombre()
    {

        return $this->nombre;
    }

    /**
     * Get the [model] column value.
     * 
     * @return     string
     */
    public function getModel()
    {

        return $this->model;
    }

    /**
     * Get the [method] column value.
     * 
     * @return     string
     */
    public function getMethod()
    {

        return $this->method;
    }

    /**
     * Get the [parameters] column value.
     * 
     * @return     string
     */
    public function getParameters()
    {

        return $this->parameters;
    }

    /**
     * Get the [kw_parameters] column value.
     * 
     * @return     string
     */
    public function getKwParameters()
    {

        return $this->kw_parameters;
    }

    /**
     * Get the [output] column value.
     * 
     * @return     string
     */
    public function getOutput()
    {

        return $this->output;
    }

    /**
     * Set the value of [id] column.
     * 
     * @param      int $v new value
     * @return     void
     */
    public function setId($v)
    {

        // Since the native PHP type for this column is integer,
        // we will cast the input value to an int (if it is not).
        if ($v !== null && !is_int($v) && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::ID;
        }

    } // setId()

    /**
     * Set the value of [step_id] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setStepId($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->step_id !== $v) {
            $this->step_id = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::STEP_ID;
        }

    } // setStepId()

    /**
     * Set the value of [pro_uid] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setProUid($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->pro_uid !== $v || $v === '') {
            $this->pro_uid = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::PRO_UID;
        }

    } // setProUid()

    /**
     * Set the value of [nombre] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setNombre($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->nombre !== $v) {
            $this->nombre = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::NOMBRE;
        }

    } // setNombre()

    /**
     * Set the value of [model] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setModel($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->model !== $v) {
            $this->model = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::MODEL;
        }

    } // setModel()

    /**
     * Set the value of [method] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setMethod($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->method !== $v) {
            $this->method = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::METHOD;
        }

    } // setMethod()

    /**
     * Set the value of [parameters] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setParameters($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->parameters !== $v) {
            $this->parameters = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::PARAMETERS;
        }

    } // setParameters()

    /**
     * Set the value of [kw_parameters] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setKwParameters($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->kw_parameters !== $v) {
            $this->kw_parameters = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::KW_PARAMETERS;
        }

    } // setKwParameters()

    /**
     * Set the value of [output] column.
     * 
     * @param      string $v new value
     * @return     void
     */
    public function setOutput($v)
    {

        // Since the native PHP type for this column is string,
        // we will cast the input to a string (if it is not).
        if ($v !== null && !is_string($v)) {
            $v = (string) $v;
        }

        if ($this->output !== $v) {
            $this->output = $v;
            $this->modifiedColumns[] = OdooStepStepPeer::OUTPUT;
        }

    } // setOutput()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (1-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param      ResultSet $rs The ResultSet class with cursor advanced to desired record pos.
     * @param      int $startcol 1-based offset column which indicates which restultset column to start with.
     * @return     int next starting column
     * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate(ResultSet $rs, $startcol = 1)
    {
        try {

            $this->id = $rs->getInt($startcol + 0);

            $this->step_id = $rs->getString($startcol + 1);

            $this->pro_uid = $rs->getString($startcol + 2);

            $this->nombre = $rs->getString($startcol + 3);

            $this->model = $rs->getString($startcol + 4);

            $this->method = $rs->getString($startcol + 5);

            $this->parameters = $rs->getString($startcol + 6);

            $this->kw_parameters = $rs->getString($startcol + 7);

            $this->output = $rs->getString($startcol + 8);

            $this->resetModified();

            $this->setNew(false);

            // FIXME - using NUM_COLUMNS may be clearer.
            return $startcol + 9; // 9 = OdooStepStepPeer::NUM_COLUMNS - OdooStepStepPeer::NUM_LAZY_LOAD_COLUMNS).

        } catch (Exception $e) {
            throw new PropelException("Error populating OdooStepStep object", $e);
        }
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      Connection $con
     * @return     void
     * @throws     PropelException
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete($con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(OdooStepStepPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            OdooStepStepPeer::doDelete($this, $con);
            $this->setDeleted(true);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Stores the object in the database.  If the object is new,
     * it inserts it; otherwise an update is performed.  This method
     * wraps the doSave() worker method in a transaction.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update
     * @throws     PropelException
     * @see        doSave()
     */
    public function save($con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(OdooStepStepPeer::DATABASE_NAME);
        }

        try {
            $con->begin();
            $affectedRows = $this->doSave($con);
            $con->commit();
            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Stores the object in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      Connection $con
     * @return     int The number of rows affected by this insert/update and any referring
     * @throws     PropelException
     * @see        save()
     */
    protected function doSave($con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;


            // If this object has been modified, then save it to the database.
            if ($this->isModified()) {
                if ($this->isNew()) {
                    $pk = OdooStepStepPeer::doInsert($this, $con);
                    $affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
                                         // should always be true here (even though technically
                                         // BasePeer::doInsert() can insert multiple rows).

                    $this->setId($pk);  //[IMV] update autoincrement primary key

                    $this->setNew(false);
                } else {
                    $affectedRows += OdooStepStepPeer::doUpdate($this, $con);
                }
                $this->resetModified(); // [HL] After being saved an object is no longer 'modified'
            }

            $this->alreadyInSave = false;
        }
        return $affectedRows;
    } // doSave()

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return     array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param      mixed $columns Column name or an array of column names.
     * @return     boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();
            return true;
        } else {
            $this->validationFailures = $res;
            return false;
        }
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param      array $columns Array of column names to validate.
     * @return     mixed <code>true</code> if all validations pass; 
                   array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            if (($retval = OdooStepStepPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = OdooStepStepPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        return $this->getByPosition($pos);
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return     mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getStepId();
                break;
            case 2:
                return $this->getProUid();
                break;
            case 3:
                return $this->getNombre();
                break;
            case 4:
                return $this->getModel();
                break;
            case 5:
                return $this->getMethod();
                break;
            case 6:
                return $this->getParameters();
                break;
            case 7:
                return $this->getKwParameters();
                break;
            case 8:
                return $this->getOutput();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param      string $keyType One of the class type constants TYPE_PHPNAME,
     *                        TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = OdooStepStepPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getStepId(),
            $keys[2] => $this->getProUid(),
            $keys[3] => $this->getNombre(),
            $keys[4] => $this->getModel(),
            $keys[5] => $this->getMethod(),
            $keys[6] => $this->getParameters(),
            $keys[7] => $this->getKwParameters(),
            $keys[8] => $this->getOutput(),
        );
        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name peer name
     * @param      mixed $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TYPE_PHPNAME,
     *                     TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @return     void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = OdooStepStepPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return     void
     */
    public function setByPosition($pos, $value)
    {
        switch($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setStepId($value);
                break;
            case 2:
                $this->setProUid($value);
                break;
            case 3:
                $this->setNombre($value);
                break;
            case 4:
                $this->setModel($value);
                break;
            case 5:
                $this->setMethod($value);
                break;
            case 6:
                $this->setParameters($value);
                break;
            case 7:
                $this->setKwParameters($value);
                break;
            case 8:
                $this->setOutput($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TYPE_PHPNAME, TYPE_COLNAME, TYPE_FIELDNAME,
     * TYPE_NUM. The default key type is the column's phpname (e.g. 'authorId')
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return     void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = OdooStepStepPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }

        if (array_key_exists($keys[1], $arr)) {
            $this->setStepId($arr[$keys[1]]);
        }

        if (array_key_exists($keys[2], $arr)) {
            $this->setProUid($arr[$keys[2]]);
        }

        if (array_key_exists($keys[3], $arr)) {
            $this->setNombre($arr[$keys[3]]);
        }

        if (array_key_exists($keys[4], $arr)) {
            $this->setModel($arr[$keys[4]]);
        }

        if (array_key_exists($keys[5], $arr)) {
            $this->setMethod($arr[$keys[5]]);
        }

        if (array_key_exists($keys[6], $arr)) {
            $this->setParameters($arr[$keys[6]]);
        }

        if (array_key_exists($keys[7], $arr)) {
            $this->setKwParameters($arr[$keys[7]]);
        }

        if (array_key_exists($keys[8], $arr)) {
            $this->setOutput($arr[$keys[8]]);
        }

    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return     Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(OdooStepStepPeer::DATABASE_NAME);

        if ($this->isColumnModified(OdooStepStepPeer::ID)) {
            $criteria->add(OdooStepStepPeer::ID, $this->id);
        }

        if ($this->isColumnModified(OdooStepStepPeer::STEP_ID)) {
            $criteria->add(OdooStepStepPeer::STEP_ID, $this->step_id);
        }

        if ($this->isColumnModified(OdooStepStepPeer::PRO_UID)) {
            $criteria->add(OdooStepStepPeer::PRO_UID, $this->pro_uid);
        }

        if ($this->isColumnModified(OdooStepStepPeer::NOMBRE)) {
            $criteria->add(OdooStepStepPeer::NOMBRE, $this->nombre);
        }

        if ($this->isColumnModified(OdooStepStepPeer::MODEL)) {
            $criteria->add(OdooStepStepPeer::MODEL, $this->model);
        }

        if ($this->isColumnModified(OdooStepStepPeer::METHOD)) {
            $criteria->add(OdooStepStepPeer::METHOD, $this->method);
        }

        if ($this->isColumnModified(OdooStepStepPeer::PARAMETERS)) {
            $criteria->add(OdooStepStepPeer::PARAMETERS, $this->parameters);
        }

        if ($this->isColumnModified(OdooStepStepPeer::KW_PARAMETERS)) {
            $criteria->add(OdooStepStepPeer::KW_PARAMETERS, $this->kw_parameters);
        }

        if ($this->isColumnModified(OdooStepStepPeer::OUTPUT)) {
            $criteria->add(OdooStepStepPeer::OUTPUT, $this->output);
        }


        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return     Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(OdooStepStepPeer::DATABASE_NAME);

        $criteria->add(OdooStepStepPeer::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return     int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param      int $key Primary key.
     * @return     void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of OdooStepStep (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @throws     PropelException
     */
    public function copyInto($copyObj, $deepCopy = false)
    {

        $copyObj->setStepId($this->step_id);

        $copyObj->setProUid($this->pro_uid);

        $copyObj->setNombre($this->nombre);

        $copyObj->setModel($this->model);

        $copyObj->setMethod($this->method);

        $copyObj->setParameters($this->parameters);

        $copyObj->setKwParameters($this->kw_parameters);

        $copyObj->setOutput($this->output);


        $copyObj->setNew(true);

        $copyObj->setId(NULL); // this is a pkey column, so set to default value

    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return     OdooStepStep Clone of current object.
     * @throws     PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);
        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return     OdooStepStepPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new OdooStepStepPeer();
        }
        return self::$peer;
    }
}

