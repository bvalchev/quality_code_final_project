<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 10.4.2019 г.
 * Time: 20:24
 */
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Authentication Model Class
 * The following model class will be responsible for the interaction with the users table in DB
 * @author	Boyan Valchev
 */
class Authentication_Model extends Basic_Model{
    /**
     * Overrides the method of the ancestor
     * Inserts a tuple in the users table
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $table = 'user_login';
    public function insertUser($data){
        return parent::insert($this->table, $data);
    }
    public function deleteUser($id)
    {
        return parent::delete($this->table, $id);
    }
    public function updateUser($rowId, $data){
        return parent::update($this->table, $rowId, $data);
    }
    public function isUsernameInUse($username){
        $query = $this->db->get_where($this->table, array('username'=>$username));
        return $query->num_rows()>0;
    }

    public function getUserById($id){
        $query = $this->db->get_where($this->table, array('id'=>$id));
        $arr = array();
        if($query->num_rows()>0){
            foreach ($query->result() as $row)
            {
                $arr[] = $row;
            }
        }
        return $arr;
        //return $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }
    public function getUser($username){
        $query = $this->db->get_where($this->table, array('username'=>$username));
        $arr = array();
        if($query->num_rows()>0){
            foreach ($query->result() as $row)
            {
                $arr[] = $row;
            }
        }
        return $arr;
        //return $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
    }
    public function deleteUserByName($username){
        $this->db->delete($this->table, array('username' => $username));
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }
    public function getHashedPassword($username)
    {
        $hashedPassword = null;
        $query = $this->db->get_where($this->table, array('username' => $username));
        if ($query->num_rows() == 1) {
            foreach ($query->result() as $row) {
                $hashedPassword = $row->password;
            }
        }
        return $hashedPassword;
    }
}
