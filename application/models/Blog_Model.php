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

    private $table = BLOG_TABLE_NAME;

    public function insertBlogPost($data){
        return $this->insert($this->table, $data);
    }

    public function updateBlogPost($rowId, $data){
        return $this->update($this->table, $rowId, $data, 'blog_id');
    }

    public function executeGetOperation($arrayForLikeOperator){
        return $this->convertArrayToJson($this->executeBasicGetOperation($this->table, $arrayForLikeOperator ));
    }

    public function deleteBlogPostById($id){
        $this->db->delete($this->table, array('blog_id' => $id));
        $hasAffectedRows = $this->db->affected_rows()>0;
        return $hasAffectedRows;
    }
}