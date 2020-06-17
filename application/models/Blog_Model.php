<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 15.2.2020 г.
 * Time: 16:23
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Blog_Model extends Basic_Model{
    /**
     * Overrides the method of the ancestor
     * Inserts a tuple in the clients table
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $table = 'blog';

    public function insertBlogPost($data){
        return parent::insert($this->table, $data);
    }

    public function updateBlogPost($rowId, $data){
        return parent::update($this->table, $rowId, $data, 'blog_id');
    }

    public function getOperation($filterDataAsArray, $arrayForLikeOperator, $limit, $offset, $sortField = null, $sortOrder = null){
        return parent::basicGetOperation($this->table, $filterDataAsArray, $arrayForLikeOperator, $limit, $offset, $sortField, $sortOrder);
    }

    public function deleteBlogPostById($id){
        $this->db->delete($this->table, array('blog_id' => $id));
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }

    public function getPicturesToUpdate(){
        
        $this->db->select('pictureName');
        $this->db->from($this->tableForPicUpdate);
        $query = $this->db->get();
        $picArray = array();
        if($query->num_rows()>0){
            foreach ($query->result() as $row)
            {
                $picArray[] = $row;
            }
        }
        return $picArray;
    }
}