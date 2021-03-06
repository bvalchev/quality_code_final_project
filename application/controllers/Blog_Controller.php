<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 15.2.2020 г.
 * Time: 16:31
 */

/**
 * Include the interface from the interface folder
 */


/**
 * Class Blog_Controller
 * The following class inherits Basic_Controller class and is responsible for the handling of blog operations
 * @Author Boyan Valchev
 */
class Blog_Controller extends Basic_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Blog_Model');
        $this->uploadPath = realpath(APPPATH . PICTURES_UPLOAD_LOCATION);
    }
   

    /**
     * The following function is used to call the right method,
     * depending on the method used.
     * Example url: www.websitename.com/posts
     * Depending on the method used the function will resolve to:
     * Method GET: Resolves to private function getBlogPosts()
     *      The stated function is responsible for getting blog posts
     *      Consideres the following URL parameters
     *          offset - The value of the offer offset
     *          limit - The value of the offer count limit
     *          sortField - The value of the field to sort on
     *          sortOrder - Expects ASC or DESC depending on the way the items to be sorted
     *      Returnes an array of all desired blog posts
     *      IMPORTANT - For security concerns all of the tags will be escaped!
     * Method POST: Resolves to private function insertBlogPost()
     *      The stated function inserts blog posts
     *      The function takes the parameters from the post body.
     *      The parameters should be provided as valid JSON.
     *      Should there be html tags in the parameter, they should be escaped
     *      The following parameters are considered:
     *          blog_post_name - {string} - The name of the post
     *          blog_main_pic - {string} - The name of the main pic.
     *          blog_post_heading - {string} - The heading of the post (not sure if we will need it)
     *          blog_post_body - {string} - The blog body generated by TinyMCE
     *          blog_post_tags - {string} - Tags for the post
     *          created_by - {string} - The username of the user who created the post - Can be taken from session info
     *          creation_date - {date} - The date of creation
     * Method PUT: Resolves to private function updateBlogPost()
     *      The following function updates blog posts
     *      The takes the parameters from the post body.
     *      The parameters should be provided as valid JSON.
     *      Should there be html tags in the parameter, they should be escaped
     *      The following parameters are considered:
     *          blog_id - {id_number} - The id of the blog post.
     *          blog_post_name - {string} - The name of the post.
     *          blog_main_pic - {string} - The name of the main pic.
     *          blog_post_heading - {string} - The heading of the post (not sure if we will need it)
     *          blog_post_body - {string} - The blog body generated by TinyMCE
     *          blog_post_tags - {string} - Tags for the post
     *          created_by - {string} - The username of the user who created the post - Can be taken from session info
     *          creation_date - {date} - The date of creation
     * Method DELETE: Resolves to private function deleteBlogPost()
     *      The following function deletes a blog post
     *      The function expects DELETE method to be used for the request
     *      The function consideres the following parameters from the request body
     *          blog_id {id_number} - The id for the blog post to delete  
     */
    public function index($method = null, $parameters = null){
        $usedMethod = $method;
        if($usedMethod == null){
            $usedMethod = $this->input->method();
            if(!$this->isSessionSet()){
                return;
            }
        }

        if(strtoupper($usedMethod) == 'GET'){
            return $this->getBlogPosts();
        }else if(strtoupper($usedMethod) == 'POST'){
            return $this->handlePostMethod($parameters);
        }else if(strtoupper($usedMethod) == "DELETE"){
            return $this->deleteBlogPost($parameters);
        }
    }

    /**
     * The following function is endpoint handler for uploading pictures
     */
    public function uploadPicture(){
        if(!empty($_FILES[DEFAULT_UPLOAD_FILE_NAME]['name'])) {
            $pictureName = $this->uploadFile($this->uploadPath, ALLOWED_FILE_TYPES, DEFAULT_UPLOAD_FILE_NAME);
            if($pictureName){
                echo $this->uploadPath.$pictureName;
                return;
            }else{
                $this->echoJsonResponse(FILE_UPLOAD_ERROR_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
                return;
            }
        }
    }

    /**
     * The following function is used to get static view for crawlers.
     */
    public function getStaticBlogView(){
        $query = $this->db->select('blog_post_heading, blog_main_pic')
             ->where('blog_id', $_GET['blog_id'])
             ->get(BLOG_TABLE_NAME);
     $resultArray = $query->result_array()[0];
     $resultArray['title'] = $resultArray['blog_post_heading'];
     $resultArray['main_image'] = $resultArray['blog_main_pic'];
     $this->load->view('staticView', $resultArray);
 }
    
    private $keysForLikeOperatorInGetRequest = array('name', 'email');
    private $keysForFileUpload = array('blog_main_pic');

    private $uploadPath;
    
    /**
     * The following function is responsible for getting blog posts
     * Example url: www.websitename.com/posts with method GET 
     * Consideres as URL parameters
     *      offset - The value of the offer offset
     *      limit - The value of the offer count limit
     *      sortField - The value of the field to sort on
     *      sortOrder - Expects ASC or DESC depending on the way the items to be sorted
     * @return array of all desired blog posts
     * IMPORTANT - For security concerns all of the tags will be escaped!
     */
    private function getBlogPosts($method = null){
        if(!$this->areAllKeysExisting($_GET, BLOG_TABLE_NAME)){
            return;
        }    
         
        $result = $this->Blog_Model->executeGetOperation($this->keysForLikeOperatorInGetRequest);
        if(!$result) {
            $this->echoJsonResponse(ERROR_OCCURRED_MESSAGE, UNSUCCESSFUL_ERROR_CODE);
            return;
        }else{
            echo $result;
            return $result;
        }
    }

    private function getPostData($postData){
        if(!$this->isJson($postData)) {
            return;
        }
        $decodedData = json_decode($postData, true);
        $decodedData['creation_date'] = date('Y/m/d h:i:s a', time());
        if(USE_SESSION){
            $decodedData['created_by'] =  $_SESSION['id'];
        }

        return $decodedData;
    }

    private function handlePostMethod($parameters = null){ 
        if(!$parameters){
            $parameters = file_get_contents('php://input');
        }
        $postData = $this->getPostData($parameters);

        if(!$this->areAllKeysExisting($postData, BLOG_TABLE_NAME)){
            return;
        }

        foreach ($this->keysForFileUpload as $value) {
            $this->uploadPicture();
        }

        if(isset($postData['blog_id'])){
           return $this->updateBlogPost($postData);
        }else{
            return $this->insertBlogPost($postData);
        }
    }

    private function insertBlogPost($insertTupleData){
        return $this->executeInsertOperation(array($this->Blog_Model, 'insertBlogPost'), $insertTupleData);
    }

    /**
     * The following function updates blog posts
     * Example url: www.websitename.com/posts with method PUT
     * REQUIRES PUT METHOD!
     * The takes the parameters from the post body.
     * The following parameters are considered:
     *      blog_id - {id_number} - The id of the blog post.
     *      blog_post_name - {string} - The name of the post.
     *      blog_main_pic - {string} - The name of the main pic.
     *      blog_post_heading - {string} - The heading of the post (not sure if we will need it)
     *      blog_post_body - {string} - The blog body generated by TinyMCE
     *      blog_post_tags - {string} - Tags for the post
     *      created_by - {string} - The username of the user who created the post - Can be taken from session info
     *      creation_date - {date} - The date of creation
     */
    private function updateBlogPost($updateTupleData){
        return $this->executeUpdateOperation(array($this->Blog_Model, 'updateBlogPost'), $updateTupleData["blog_id"], $updateTupleData);
    }

    /**
     * The following function deletes a blog post
     * The function expects DELETE method to be used for the request
     * Example url: www.websitename.com/posts with method DELETE
     * The function consideres the following parameters from the request body
     *      blog_id {id_number} - The id for the blog post to delete
     */
    private function deleteBlogPost($parameters = null){
        if(!$parameters){
            $parameters = file_get_contents('php://input', true);
        }
        if($this->isJson($parameters)) {
            $decodedData = json_decode($parameters, true);
        }

        //Check if id is 
        $isIdProvided = $this->isPropertyInArray($decodedData , 'blog_id');
        if(!$isIdProvided){
            return $isIdProvided;
        }

        if(!$this->Blog_Model->deleteBlogPostById($decodedData["blog_id"])){
            return $this->echoJsonResponse(DELETE_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
           
        }else{
            return $this->echoJsonResponse(DELETE_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_CODE);
        }
    }
}