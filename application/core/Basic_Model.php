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
    private $table;
    /**
     * Constructor, which calls the constructor of CI_Model
     */
    public function __construct() {
        parent::__construct();
        $this->offersTableToUpdate = $this->getOffersTableToUpdate();
        $this->offersTableToRead = $this->getOffersTableToRead();
        $this->datesForOffersTableToUpdate = $this->getDatesForOffersTableToUpdate();
        $this->datesForOffersTableToRead = $this->getDatesForOffersTableToRead();
        $this->hotelsForOffersTableToUpdate = $this->getHotelsForOffersTableToUpdate();
        $this->hotelsForOffersTableToRead = $this->getHotelsForOffersTableToRead();
    }

    private $tableForPicUpdate = 'pictures_optimization';
    protected $settingsTable = 'settings';
    protected $offersTableToUpdate;
    protected $offersTableToRead;
    protected $datesForOffersTableToUpdate;
    protected $datesForOffersTableToRead;
    protected $hotelsForOffersTableToUpdate;
    protected $hotelsForOffersTableToRead;

    protected function setTable($tableName){
        $this->table = $tableName;
    }
    protected function getTable(){
        return $this->table;
    }

    /**
     * The following function is used by all ancestors to insert single row in the DB
     * @param $tableName - the name of the table we want to insert in
     * @param $data - json or array of the data to be inserted
     * @return $hasAffectedRows
     */
    protected  function insert($tableName, $data){
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

        /*if($this->isJson($data)){
            $dataArray = json_decode($data);
        }else{
            $dataArray = $data;
        }*/
       /* var_dump($data);
        die();*/
        $this->db->where($specialPrimaryKey, $rowId);
        $this->db->update($tableName, $data);
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }

    /*public function uploadPic($Picture,$id){
        $this->db->select('*');
        $this->db->from('user_login');
        $this->db->where(array('id'=>$id));
        $this->db->limit(1);
        $query = $this->db->get();
        if($query->num_rows()>0){
            foreach($query->result() as $row) {
                try{
                    $this->db->set(array('profile_image'=>$Picture));
                    $this->db->where(array('id'=>$row->id));
                    $this->db->update('user_login');
                }catch(\Exception $e){
                    die($e->getMessage());
                }

                if ($this->db->affected_rows() > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        else{
            return false;
        }
    }*/

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

    /*/**
     * DEPRECATED
     * The following function is used by all ancestors to get data from selected table
     * @param $tableName - the name of the table we want to get all the data from
     * @param $whereConditionData - conditions we want to have either as an array or in JSON format
     * @param $limit - number to rows the query should return
     * @param $offset
     * @return $hasAffectedRows

    public function getAll($tableName, $whereConditionData ,$limit, $offset){
        $dataArray = $whereConditionData;
        if (isset($whereConditionData) && $this->isJson($whereConditionData)){
            $dataArray = json_decode($whereConditionData);
        }
        $query = $this->db->get_where($tableName, $dataArray, $limit, $offset);
        $this->convertArrayToJson($query);
    }*/

    private function addAndIfNeeded($query, $shouldAddAnd){
        $result = $query;
        if($shouldAddAnd)
        {
            $result = $result . " AND ";
        }
        return $result;
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
    public function basicGetOperation($tableName, $filterDataAsArray, $arrayForLikeOperator, $limit, $offset, $sortField = null, $sortOrder = null){
        $shouldAddAnd = false;
        if($arrayForLikeOperator != []){
            $this->db->group_start();
            foreach ($arrayForLikeOperator as $key => $value) {
                $this->db->or_like($key, $value);
            }
            $this->db->group_end();
        }
        if($sortField != null){
            if($sortOrder != null && in_array($sortOrder, array('ASC', 'DESC'))) {
                $this->db->order_by($sortField, $sortOrder);
            }else{
                $this->db->order_by($sortField, 'asc');
            }
        }
        $query = $this->db->get_where($tableName, $filterDataAsArray, $limit, $offset);
        return $this->convertArrayToJson($query);
        //mysqli_connect("localhost", "root", '', "terminal");
        // $query = "SELECT * FROM offers2 WHERE ";
        //          //JOIN ". $this->datesForOffersTableToRead . " dates ON dates.offer_pid = o.pid 
        //          //JOIN ". $this->hotelsForOffersTableToRead . " hotels ON hotels.offer_pid = o.pid WHERE ";

        // if($arrayForLikeOperator != []){
        //     $shouldAddAnd = true;
        //     $query = $query . " ( ";
        //     foreach ($arrayForLikeOperator as $key => $value) {
        //         $query = $query . $key . " LIKE ". $value . " OR ";
        //     }
        //     $query = $query . " ) ";
        // }

        // if(array_key_exists('startDate', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //     $query = $query . " `date_start` = ". date($filterDataAsArray['startDate']). " ";
        // }
        // if(array_key_exists('endDate', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //     $query = $query . " date_end = ". date($filterDataAsArray['endDate']). " ";
        // }
        // if(array_key_exists('afterDate', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //     $query = $query . " date_start > ". date($filterDataAsArray['afterDate']). " ";
        // }
        // if(array_key_exists('beforeDate', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //     $query = $query . " date_end < ". date($filterDataAsArray['beforeDate']). " ";
        // }
        // if(array_key_exists('country', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //    // $query = $query . " ( ";
        //     $query = $query .  " `country` LIKE '%".$filterDataAsArray['country']."%' OR `description` LIKE '%".$filterDataAsArray['country']."%' OR `description_clean` LIKE '%".$filterDataAsArray['country']."%' ";
        // }
        // if(array_key_exists('maxPrice', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //     $query = $query . " min_price < ". date($filterDataAsArray['maxPrice']). " ";
        // }
        // if(array_key_exists('minPrice', $filterDataAsArray)){
        //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        //     $shouldAddAnd = true;
        //     $query = $query . " min_price > ". date($filterDataAsArray['minPrice']). " ";
        // }
        // // if($sortField != null){
        // //     $query = $this->addAndIfNeeded($query, $shouldAddAnd);
        // //     $shouldAddAnd = true;
        // //     if($sortOrder != null && in_array($sortOrder, array('ASC', 'DESC'))) {
        // //         $this->db->order_by($sortField, $sortOrder);
        // //     }else{
        // //         $this->db->order_by($sortField, 'asc');
        // //     }
        // // }
        // $udri = $this->db->query($query);
        // var_dump($query);
        // var_dump($udri->result());
        // die();
        //$query = $this->db->get_where($this->offersTableToRead, $filterDataAsArray, $limit, $offset);
        return $this->convertArrayToJson($query);
    }

    /**
     * The following function gets tuple by id
     * @param $tableName
     * @param $id - id of the wanted tuple
     * @return $hasAffectedRows
     */
    public function getById($tableName, $id){
        $query = $this->db->get_where($tableName, array('id' => $id));
        $this->convertArrayToJson($query);
    }


    public function insertPictureForOptimization( $data){
        return $this->insert($this->tableForPicUpdate, $data);
    }

    protected function getSettingsTable($setting_name)
    {
        $this->db->select('setting_value');
        $query = $this->db->get_where($this->settingsTable, array('setting_name' => $setting_name));
        return $query->row()->setting_value;
    }

    public function getOffersTableToUpdate()
    {
       return $this->getSettingsTable('offersTableToUpdate');
    }

    public function getOffersTableToRead()
    {
        return $this->getSettingsTable('offersTableToRead');
    }
    
    public function getDatesForOffersTableToRead()
    {
        return $this->getSettingsTable('datesForOffersTableToRead');
    }
    
    public function getDatesForOffersTableToUpdate()
    {
        return $this->getSettingsTable('datesForOffersTableToUpdate');
    }

    public function getHotelsForOffersTableToRead()
    {
        return $this->getSettingsTable('hotelsForOffersTableToRead');
    }

    public function getHotelsForOffersTableToUpdate()
    {
        return $this->getSettingsTable('hotelsForOffersTableToUpdate');
    }
}
?>