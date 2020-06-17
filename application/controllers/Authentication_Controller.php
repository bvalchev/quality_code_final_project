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
        if(!parent::isDesiredMethodUsed('post')){
            return;
        };

        $postParams = file_get_contents('php://input', true);
        if(!$this->isJson($postParams)){
            return;
        }

        $insertTupleData = json_decode($postParams);
        $insertTupleData = parent::sanitizeInput($insertTupleData);
        $username = $insertTupleData->username;
        $password =  $insertTupleData->password;

        if(!$this->Authentication_Model->isUsernameInUse($username)){
            $this->echoJsonResponse(USER_NOT_FOUND_MESSAGE, BAD_REQUEST_ERROR_CODE);
            return;
        }
        else {
            $userInfo = $this->Authentication_Model->getUser($username);
            $hashedPassword = $userInfo[0]->password;
            if (password_verify($password, $hashedPassword)) {
                $this->setUserSession($username, $userInfo);
                return;
            }
            else {
                $this->echoJsonResponse(INVALID_PASSWORD_MESSAGE, BAD_REQUEST_ERROR_CODE);
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
        //Check if user is admin
        /*if(!$this->isAdmin()){
             return;
        }*/
        if(!$this->isSessionSet() || !parent::isDesiredMethodUsed('post')){
            return;
        }

        $dataObject = file_get_contents('php://input', true);
        if(!$this->isJson($dataObject)){
            return;
        }
        $insertTupleData = json_decode($dataObject);
        $insertTupleData = parent::sanitizeInput($insertTupleData);
        if(!$this->validateInput($insertTupleData)){
            $this->echoJsonResponse(VALIDATION_ERROR_MESSAGE, BAD_REQUEST_ERROR_CODE);
            return;
        }
        $insertTupleData->password = $this->encrypt($insertTupleData->password);
        if(!$this->Authentication_Model->isUsernameInUse($insertTupleData->username)) {
            $this->executeInsertUserOperation($insertTupleData);
        }
        else{
            $this->echoJsonResponse(USERNAME_TAKEN_MESSAGE, BAD_REQUEST_ERROR_CODE);
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
        if(!$this->isSessionSet() || !parent::isDesiredMethodUsed('post')){
            return;
        }
        $inputDataObject = file_get_contents('php://input', true);
        if(!$this->isJson($inputDataObject)){
            return;
        }
        $insertTupleData = json_decode($inputDataObject);
        $sanitizedInputData = parent::sanitizeInput($insertTupleData);
        $user = $this->Authentication_Model->getUser($sanitizedInputData->username);
        if(!empty($user)){
            $user = $user[0];
        }else{
            $this->echoJsonResponse(USER_NOT_FOUND, BAD_REQUEST_ERROR_CODE);
            return;
        }
    
        if(!password_verify($sanitizedInputData->password, $user->password)){
            $this->echoJsonResponse(INVALID_PASSWORD_MESSAGE, BAD_REQUEST_ERROR_CODE);
            return;
        }

        if(!$this->Authentication_Model->updateUser($user->id, array('password' => $this->encrypt($sanitizedInputData->newPassword)))){
            $this->echoJsonResponse(UPDATE_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
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
        if(!$this->isSessionSet() || !parent::isDesiredMethodUsed('delete')){
            return;
        }
        $jsonObject = file_get_contents('php://input', true);
        if(!$this->isJson($jsonObject)) {
            return;
        }
        $userIdObject = json_decode($jsonObject, true);
        if(!parent::checkIfPropertyIsSet($userIdObject, 'username')){
            return;
        }
        if(!$this->Authentication_Model->isUsernameInUse($userIdObject->username)){
            $this->echoJsonResponse(USER_NOT_FOUND, BAD_REQUEST_ERROR_CODE);
            return;
        }

        $message = DELETE_SUCCESSFUL_MESSAGE;
        if(!$this->Authentication_Model->deleteUserByName($userIdObject->username)){
            $message = DELETE_FAILED_MESSAGE;
        }
        $this->echoJsonResponse(DELETE_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_ERROR_CODE);
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
        $this->echoJsonResponse("Logged out", SUCCESSFUL_REQUEST_ERROR_CODE);
        return;
    }

    private function setUserSession($username, $userInfo){
        $position = $userInfo[0]->position;
        $id = $userInfo[0]->id;
        $data = array(
            'id' => $id,
            'username' => $username,
            'position' => $position,
            'logged_in' => TRUE
        );
        $this->session->set_userdata($data);
        $this->getSessionDetails();
    }

    private function isAdmin(){
        $isAdmin = false;
        if(isset($_SESSION['position']) && ($_SESSION['position'] = 'Administrator' || $_SESSION['position'] == 1)){
            $isAdmin = true;
        }
        return $isAdmin;
    }

    private function isSessionSet(){
        if(isset($_SESSION['username'])){
            $isSessionSet = true;
        }
        else{
            $this->echoJsonResponse(SESSION_EXPIRED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
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
                $this->echoJsonResponse("No rights to perform operation", BAD_REQUEST_ERROR_CODE);
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
            $this->echoJsonResponse(INSERT_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
            return;
        }
        else{
            $this->echoJsonResponse(INSERT_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_ERROR_CODE);
            return;
        }
    }
}