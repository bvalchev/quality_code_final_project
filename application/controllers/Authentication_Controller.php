<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 10.4.2019 г.
 * Time: 20:40
 */

/**
 * Class Authentication_Controller
 * The following class is responsible for the handling of data connected with users
 */

/**
 * Include the interface from the interface folder
 */
require_once APPPATH.'interfaces/Authentication_Controller_Interface.php';

/**
 * Class Authentication_Controller
 * The following class inherits Basic_Controller class and is responsible for the handling of authentication of users
 * @Author Boyan Valchev
 */
class Authentication_Controller extends Basic_Controller implements Authentication_Controller_Interface{

    /**
     * Authentication_Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Authentication_Model');
    }



    /**
     * The following function is responsible for login
     * Example url to access the function www.websitename.com/index.php/login
     * It requires JSON object POST parameter, containing the following information
     * JSON object Parameter 1: username
     * JSON object Parameter 2: password
     * The function sets the session token
     */
    public function login(){
       
        //Check if post method is used
        if(!parent::isDesiredMethodUsed('post')){
            return;
        };

        //Get post params
        $dataObject = file_get_contents('php://input', true);

        //If the object is json, decode it, else print an error
        if($this->isJson($dataObject)){
            $insertTupleData = json_decode($dataObject);
        }else{
            $this->echoJsonResponse("JSON object expected", $this->badRequestErrorCode);
            return;
        }

        //Sanitize input
        $insertTupleData = parent::sanitizeInput($insertTupleData);

        $username = $insertTupleData->username;
        $password =  $insertTupleData->password;

        //If user does not exist set error
        if(!$this->Authentication_Model->isUsernameInUse($username)){
            $this->echoJsonResponse("User not found", $this->badRequestErrorCode);
            return;
        }
        else {
            //Get hash from DB
            $userInfo = $this->Authentication_Model->getUser($username);
            $hashedPassword = $userInfo[0]->password;
            

            //If password matches - set session cookie
            if (password_verify($password, $hashedPassword)) {
                $position = $userInfo[0]->position;
                $id = $userInfo[0]->id;
                $data = array(
                    'id' => $id,
                    'username' => $username,
                    'position' => $position,
                    'logged_in' => TRUE
                );
                $this->session->set_userdata($data);
                //Return session details
                $this->getSessionDetails();
                return;

            } //Else set error message
            else {
                $this->echoJsonResponse("Invalid password", $this->badRequestErrorCode);
                return;
            }
        }
    }



    /**
     * The following function is responsible for user insertion
     * Example url to access the function www.websitename.com/index.php/user/register
     * It requires JSON object POST parameter, containing the following information
     * JSON object Parameter 1: username
     * JSON object Parameter 2: password
     * JSON object Parameter 3: position
     * The function inserts the user into the database
     */
    public function insertUser(){
        //check if session is set
        //if(!isset($_SESSION['id']) && $this->useSession){
         //   $this->echoJsonResponse("Session not set", $this->unsuccessfulRequestErrorCode);
         //   return;
        //}
        //Check if user is admin
        /*if(!$this->isAdmin()){
             return;
        }*/
        //Check if post method is used
        if(!parent::isDesiredMethodUsed('post')){
            return;
        };

        $dataObject = file_get_contents('php://input', true);

        //$dataObject = $this->input->post('jsonDataObject');

        //If the object is json, decode it, else print an error
        if($this->isJson($dataObject)){
            $insertTupleData = json_decode($dataObject);
        }

        //Sanitize input
        $insertTupleData = parent::sanitizeInput($insertTupleData);

        //Validate input
        if(!$this->validateInput($insertTupleData)){
            $this->echoJsonResponse("Validation of input failed", $this->badRequestErrorCode);
            return;
        }

        //Encrypt password
        $insertTupleData->password = $this->encrypt($insertTupleData->password);

        //Check if username is free
        if(!$this->Authentication_Model->isUsernameInUse($insertTupleData->username)) {
            ////If username free insert data into table
            $this->executeInsertUserOperation($insertTupleData);
        }
        //else Display error message
        else{
            $this->echoJsonResponse("Username taken", $this->badRequestErrorCode);
            return;
        }
    }




    /**
     * The following function is responsible for password update
     * Example url to access the function www.websitename.com/index.php/password/update
     * It requires JSON object as POST parameter, containing the following information
     * JSON object Parameter 1: username
     * JSON object Parameter 2: password
     * JSON object Parameter 3: newPassword
     */
    public function updatePassword(){
        //check if session is set
        if(!isset($_SESSION['id']) && $this->useSession){
            $this->echoJsonResponse("Session not set", $this->unsuccessfulRequestErrorCode);
            return;
        }

        //Check if post method is used
        if(!parent::isDesiredMethodUsed('post')){
            return;
        };

        $inputDataObject = file_get_contents('php://input', true);
        //get data
        if($this->isJson($inputDataObject)){
            $insertTupleData = json_decode($inputDataObject);
        }else{
            //echo "JSON object expected";
            return;
        }

        //Sanitize input
       $sanitizedInputData = parent::sanitizeInput($insertTupleData);

        //check if user has edit rights
        /*if(!$this->hasEditRights($sanitizedInputData->username)){
            return;
        }*/

        //get user data
        $user = $this->Authentication_Model->getUser($sanitizedInputData->username);
        if(!empty($user)){
            $user = $user[0];
        }else{
            $this->echoJsonResponse("User not found", $this->badRequestErrorCode);
            return;
        }
    

        //TO DO CHECK IF NEEDED
        //Check if provided old password and password in db match
        if(!password_verify($sanitizedInputData->password, $user->password)){
            $this->echoJsonResponse("Incorrect password", $this->badRequestErrorCode);
            return;
        }

        //update data
        if(!$this->Authentication_Model->updateUser($user->id, array('password' => $this->encrypt($sanitizedInputData->newPassword)))){
            $this->echoJsonResponse("Update operation failed", $this->unsuccessfulRequestErrorCode);
            return;
        };
        $this->logout();
    }




    /**
     * The following function is responsible for deleting a user
     * Example url to access the function www.websitename.com/index.php/user/delete
     * It requires JSON object as DELETE request parameter, containing the following information
     * JSON object Parameter 1: username
     */
    public function deleteUser(){
        //Check if session is set
        if(!isset($_SESSION['id']) && $this->useSession){
            $this->echoJsonResponse("Session not set", $this->unsuccessfulRequestErrorCode);
            return;
        }
        //Check if user is admin
        /*if(!$this->isAdmin()){
            return;
        }*/
        //Check if delete method is used
        if(!parent::isDesiredMethodUsed('delete')){
            return;
        };
        $jsonObject = file_get_contents('php://input', true);
        if($this->isJson($jsonObject)) {
            $userIdObject = json_decode($jsonObject, true);
        }else{
            $this->echoJsonResponse("JSON object expected", $this->badRequestErrorCode);
            return;
        }

        //Check if username exists
        if(!parent::checkIfPropertyIsSet($userIdObject, 'username')){
            return;
        }

        //If user does not exist set error
        if(!$this->Authentication_Model->isUsernameInUse($userIdObject->username)){
            $this->echoJsonResponse("User not found", $this->badRequestErrorCode);
            return;
        }

        if(!$this->Authentication_Model->deleteUserByName($userIdObject->username)){
            $this->echoJsonResponse("Delete operation failed", $this->unsuccessfulRequestErrorCode);
            return;
        }else{
            $this->echoJsonResponse("Delete operation successful", $this->successfulRequestCode);
            return;
        }
    }


    /**
     * The function checks if the user is logged in and if so returns the session data
     * in JSON format
     * Example url to access the function www.websitename.com/index.php/session
     * @return $jsonEncodedResponse - json with session data
     */
    public function getSessionDetails(){
        if(!$this->isSessionSet()){
            return;
        }
        $response = array('id' => $_SESSION['id'],
                          'username' => $_SESSION['username'],
                          'position' => $_SESSION['position'],
                          'deviceIp' => $_SERVER['REMOTE_ADDR'],
                           'sessionID' => session_id());
        $jsonEncodedResponse = json_encode($response, JSON_UNESCAPED_UNICODE );
        echo $jsonEncodedResponse;
    }




    /**
     * The following function is responsible for destroying the session
     * Example url to access the function www.websitename.com/index.php/logout
     * It unsets the session token.
     */
    public function logout(){
        $data=array('id' => '', 'username' => '', 'position' => '', 'logged_in' => '');
        $this->session->unset_userdata($data);
        $this->session->sess_destroy();
        $this->echoJsonResponse("Logged out", $this->successfulRequestCode);
        return;
    }



    /**
     * The following function checks if the user is administrator
     */
    private function isAdmin(){
        $isAdmin = false;
        if(isset($_SESSION['position']) && ($_SESSION['position'] = 'Administrator' || $_SESSION['position'] == 1)){
            $isAdmin = true;
        }
        return $isAdmin;
    }



    /**
     * The following function checks if the session is set
     */
    private function isSessionSet(){
        if(isset($_SESSION['username'])){
            $isSessionSet = true;
        }
        else{
            $this->echoJsonResponse("Session expired", $this->unsuccessfulRequestErrorCode);
            $isSessionSet = false;
        }
        return $isSessionSet;
    }



    private function hasEditRights($userToEdit){
        $hasEditRights = false;
        if($this->isSessionSet()){
            if($this->isAdmin() || $_SESSION['username'] == $userToEdit){
                $hasEditRights = true;
            }else{
                $this->echoJsonResponse("No rights to perform operation", $this->badRequestErrorCode);
            }
        }
        return $hasEditRights;
    }



    private function encrypt($password){
        $encryptedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $encryptedPassword;
    }




    private function isPositionValid($position){
        $this->form_validation->set_data(array(
            'position'    =>  $position
        ));

        $this->form_validation->set_rules('position', 'position', 'trim|required|in_list[Administrator,Manager,OfficeAssistant,Consultant,1,2,3,4]');

        return $this->form_validation->run() == TRUE;

    }




    private function isUsernameValid($username){
        $this->form_validation->set_data(array(
            'username'    =>  $username
        ));

        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[15]');

        return ($this->form_validation->run() == TRUE);


    }
    
    
    
    private function isPasswordValid($password){
        $this->form_validation->set_data(array(
            'password'    =>  $password
        ));

        $this->form_validation->set_rules('password', 'password', 'trim|required|min_length[5]|max_length[20]');


        return $this->form_validation->run() == TRUE;
    }



    private function validateInput($object){
        $inputValid = FALSE;
        if($this->isUsernameValid($object->username) && $this->isPositionValid($object->position) && $this->isPasswordValid($object->password)){
            $inputValid = TRUE;
        }
        //Display errors if present
        if(!$inputValid){
            echo json_encode($this->form_validation->error_array());
        }
        return $inputValid;
    }




    private function isPasswordMatchingHash($plainTextPassword, $hash){
        $isPasswordMatchingHash = password_verify($plainTextPassword, $hash);
        return $isPasswordMatchingHash;
    }



    private function executeInsertUserOperation($insertTupleData){
        if (!$this->Authentication_Model->insertUser($insertTupleData)) {
            $this->echoJsonResponse("Insert operation failed", $this->unsuccessfulRequestErrorCode);
            return;
        }
        else{
            $this->echoJsonResponse("Insert operation successful", $this->successfulRequestCode);
            return;
        }
    }
}