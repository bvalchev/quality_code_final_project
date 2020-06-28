<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Author: Boyan Valchev
 * Date: 24.3.2019 г.
 * Time: 20:48
 */

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Basic Model Class
 * The following class will be inherited from every other model class
 * It will contain the basic methods for database manipulation
 * @author	Boyan Valchev
 */
class Basic_Model extends CI_Model
{
    
    /**
     * Constructor, which calls the constructor of CI_Model
     */
    public function __construct() {
        parent::__construct();
        $this->initializeTableNames();
    }

    protected $updateOffersTable;
    protected $readOffersTable;
    protected $updateDatesForOffersTable;
    protected $readDatesForOffersTable;
    protected $updateHotelsForOffersTable;
    protected $readHotelsForOffersTable;

    protected function setActiveModelTable($tableName){
        $this->table = $tableName;
    }
    protected function getActiveModelTable(){
        return $this->table;
    }

    /**
     * The following function is used by all ancestors to insert single row in the DB
     * @param $tableName - the name of the table we want to insert in
     * @param $data - json or array of the data to be inserted
     * @return $hasAffectedRows
     */
    protected function insert($tableName, $data){
        $this->db->insert($tableName, $data);
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }

    /**
     * The following function is used by all ancestors to insert multiple rows in the DB
     * @param $tableName - the name of the table we want to insert in
     * @param $data - json or array of the data to be inserted
     * @return $hasAffectedRows
     */
    protected function insertMany($tableName, $data){
        $this->db->insert_batch($tableName, $data);
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }

    /**
     * The following function is used by all ancestors to delete data from the DB
     * @param $tableName - the name of the table we want to delete a tuple from
     * @param $rowId -  the id of the row which is intended to be deleted
     * @return $hasAffectedRows
     */
    protected  function delete($tableName, $rowId){
        $this->db->delete($tableName, array('id' => $rowId));
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }

    /**
     * The following function is used by all ancestors to update data from in the DB
     * @param $tableName - the name of the table we want to delete a tuple from
     * @param $rowId -  the id of the row which is intended to be updated
     * @param $data - json or array of the data to be inserted
     * @param $specialPrimaryKey - used if the primary key of the table is different than 'id'
     * @return $hasAffectedRows
     */
    protected function update($tableName, $rowId, $data, $specialPrimaryKey = 'id'){
        $this->db->where($specialPrimaryKey, $rowId);
        $this->db->update($tableName, $data);
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }

    /**
     * The following function is used by other functions to convert query result to JSON object
     * @param $query
     * @return $jsonObject
     * @see getAll()
     */
    protected function convertArrayToJson($query){
        $arr = array();
        if($query->num_rows()>0){
            foreach ($query->result() as $row)
            {
                $arr[] = $row;
                foreach($row as $key => $value){
                    $row->$key = htmlspecialchars_decode($value);
                }
            }
        }

        return $json = json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }

    /**
     * The following function checks if a given object is JSON
     * @param $object - the object for which the check should be performed
     */
    protected function isJson($object) {
        json_decode($object);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * The following function is a base function for getting data from db
     * @param $tableName
     * @param $filterDataAsArray - array containing the data which we want to filter with. Example format: [name => 'john']. This should not include the fields we want to filter with like
     * @param $arrayForLikeOperator - array containing the data which we want to filter with with LIKE operator.
     * @param $limit - limit for items
     * @param $offset
     * @param $sortField - field of the name we want to sort on
     * @param $sortOrder - ASC or DESC
     * @return json containing the data
     */
    protected function executeBasicGetOperation($tableName, $keysForLikeOperator, callable $isSpecialKeyFunction = null){
        $parameters = new QueryParameters($_GET, $keysForLikeOperator, $isSpecialKeyFunction);
        $this->addLikeOperatorToQuery($parameters->getLikeOperatorValues());
        $this->addSortToQuery($parameters->getSortField(), $parameters->getSortOrder());
        return $this->db->get_where($tableName, $parameters->getNonSpecialRequestParametes(), $parameters->getLimit(), $parameters->getOffset());
    }


    /**
     * The following function gets tuple by id
     * @param $tableName
     * @param $id - id of the wanted tuple
     * @return $hasAffectedRows
     */
    protected function getById($tableName, $id){
        $query = $this->db->get_where($tableName, array('id' => $id));
        $this->convertArrayToJson($query);
    }


    protected function getSettingValue($setting_name)
    {
        $this->db->select('setting_value');
        $query = $this->db->get_where(SETTINGS_TABLE_NAME, array('setting_name' => $setting_name));
        return $query->row()->setting_value;
    }

    protected function getUpdateOffersTable()
    {
       return $this->getSettingValue('offersTableToUpdate');
    }

    protected function getReadOffersTable()
    {
        return $this->getSettingValue('offersTableToRead');
    }
    
    protected function getReadDatesForOffersTable()
    {
        return $this->getSettingValue('datesForOffersTableToRead');
    }
    
    protected function getUpdateDatesForOffersTable()
    {
        return $this->getSettingValue('datesForOffersTableToUpdate');
    }

    protected function getReadHotelsForOffersTable()
    {
        return $this->getSettingValue('hotelsForOffersTableToRead');
    }

    protected function getUpdateHotelsForOffersTable()
    {
        return $this->getSettingValue('hotelsForOffersTableToUpdate');
    }

    private $table;

    public function getTableNames(){
        $result = array();
        $result['updateOffersTable'] = $this->getUpdateOffersTable();
        $result['readOffersTable'] = $this->getReadOffersTable();
        $result['updateDatesForOffersTable'] = $this->getUpdateDatesForOffersTable();
        $result['readDatesForOffersTable'] = $this->getReadDatesForOffersTable();
        $result['updateHotelsForOffersTable'] = $this->getUpdateHotelsForOffersTable();
        $result['readHotelsForOffersTable'] = $this->getReadHotelsForOffersTable();
        return $result;
    }

    private function initializeTableNames(){
        $this->updateOffersTable = $this->getUpdateOffersTable();
        $this->readOffersTable = $this->getReadOffersTable();
        $this->updateDatesForOffersTable = $this->getUpdateDatesForOffersTable();
        $this->readDatesForOffersTable = $this->getReadDatesForOffersTable();
        $this->updateHotelsForOffersTable = $this->getUpdateHotelsForOffersTable();
        $this->readHotelsForOffersTable = $this->getReadHotelsForOffersTable();
    }

    private function addLikeOperatorToQuery($likeOperatorValues){
        if($arrayForLikeOperator != []){
            $this->db->group_start();
            foreach ($arrayForLikeOperator as $key => $value) {
                $this->db->or_like($key, $value);
            }
            $this->db->group_end();
        }
    }

    private function addSortToQuery($sortField, $sortOrder){
        if($sortField != null){
            if($sortOrder != null && in_array($sortOrder, array('ASC', 'DESC'))) {
                $this->db->order_by($sortField, $sortOrder);
            }else{
                $this->db->order_by($sortField, 'asc');
            }
        }
    }
}
?>