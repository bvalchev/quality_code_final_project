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
 * Class Authentication_Controller
 * The following class inherits Basic_Controller class and is responsible for the handling of authentication of users
 * @Author Boyan Valchev
 */
class Authentication_Controller extends Basic_Controller{

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
    public function login($postParams = null){
        if(!$postParams){
            if(!$this->isDesiredMethodUsed('post')){
                return;
            };
            $postParams = file_get_contents('php://input', true);
        }

        if(!$this->isJson($postParams)){
            return;
        }

        $insertTupleData = json_decode($postParams);
        $insertTupleData = $this->sanitizeInput($insertTupleData);
        $username = $insertTupleData->username;
        $password =  $insertTupleData->password ;

        if(!$this->Authentication_Model->isUsernameInUse($username)){
            return $this->echoJsonResponse(USER_NOT_FOUND_MESSAGE, BAD_REQUEST_ERROR_CODE);
        }
        else {
            $userInfo = $this->Authentication_Model->getUser($username);
            $hashedPassword = $userInfo[0]->password;
            if (password_verify($password, $hashedPassword)) {
                return $this->setUserSession($username, $userInfo);
            }
            else {
                return $this->echoJsonResponse(INVALID_PASSWORD_MESSAGE, BAD_REQUEST_ERROR_CODE);
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
    public function insertUser($dataObject = null){
        //Check if user is admin
        /*if(!$this->isAdmin()){
             return;
        }*/
        if(!$this->isSessionSet()){
            return;
        }

        if(!$dataObject){
            if(!$this->isDesiredMethodUsed('post')){
                return;
            }
            $dataObject = file_get_contents('php://input', true);
        }
       
        if(!$this->isJson($dataObject)){
            return;
        }
        $insertTupleData = json_decode($dataObject);
        $insertTupleData = $this->sanitizeInput($insertTupleData);
        if(!$this->validateInput($insertTupleData)){
            return $this->echoJsonResponse(VALIDATION_ERROR_MESSAGE, BAD_REQUEST_ERROR_CODE);
        }
        $insertTupleData->password = $this->encrypt($insertTupleData->password);
       
        if(!$this->Authentication_Model->isUsernameInUse($insertTupleData->username)) {
            return $this->executeInsertUserOperation($insertTupleData);
        }
        else{
            return $this->echoJsonResponse(USERNAME_TAKEN_MESSAGE, BAD_REQUEST_ERROR_CODE);
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
    public function updatePassword($inputDataObject = null){
        if(!$this->isSessionSet()){
            return;
        }
        if(!$inputDataObject){
            if(!$this->isDesiredMethodUsed('post')){
                return;
            }
            $inputDataObject = file_get_contents('php://input', true);

        }
        if(!$this->isJson($inputDataObject)){
            return;
        }
        $insertTupleData = json_decode($inputDataObject);
        $sanitizedInputData = $this->sanitizeInput($insertTupleData);
        $user = $this->Authentication_Model->getUser($sanitizedInputData->username);
        if(empty($user)){
            return $this->echoJsonResponse(USER_NOT_FOUND, BAD_REQUEST_ERROR_CODE);
        }
        $user = $user[0];

        if(!password_verify($sanitizedInputData->password, $user->password)){
            return $this->echoJsonResponse(INVALID_PASSWORD_MESSAGE, BAD_REQUEST_ERROR_CODE);
        }

        if(!$this->Authentication_Model->updateUser($user->id, array('password' => $this->encrypt($sanitizedInputData->newPassword)))){
            return $this->echoJsonResponse(UPDATE_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
        };
        return $this->logout();
    }

    /**
     * The following function is responsible for deleting a user
     * Example url to access the function www.websitename.com/index.php/user/delete
     * It requires JSON object as DELETE request parameter, containing the following information
     * JSON object Parameter 1: username
     */
    public function deleteUser($jsonObject = null){
        if(!$this->isSessionSet()){
            return;
        }

        if(!$jsonObject){
            if( !$this->isDesiredMethodUsed('delete')){
                return;
            }
            $jsonObject = file_get_contents('php://input', true);

        }
        if(!$this->isJson($jsonObject)) {
            return;
        }
        $userData = json_decode($jsonObject, true);

        $isUsernameProvided = $this->isPropertyInArray($userData, 'username');
        if(!$isUsernameProvided){
            return $isUsernameProvided;
        }
        if(!$this->Authentication_Model->isUsernameInUse($userData['username'])){
           return $this->echoJsonResponse(USER_NOT_FOUND, BAD_REQUEST_ERROR_CODE);
        }

        $message = DELETE_SUCCESSFUL_MESSAGE;
        if(!$this->Authentication_Model->deleteUserByName($userData['username'])){
            $message = DELETE_FAILED_MESSAGE;
        }
        return $this->echoJsonResponse(DELETE_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_CODE);
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
        if(USE_SESSION){
            $data=array('id' => '', 'username' => '', 'position' => '', 'logged_in' => '');
            $this->session->unset_userdata($data);
            $this->session->sess_destroy();
        }
        return $this->echoJsonResponse(LOGGED_OUT_MESSAGE, SUCCESSFUL_REQUEST_CODE);
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
        return $this->getSessionDetails();
    }

    private function isAdmin(){
        $isAdmin = false;
        if(isset($_SESSION['position']) && ($_SESSION['position'] = 'Administrator' || $_SESSION['position'] == 1)){
            $isAdmin = true;
        }
        return $isAdmin;
    }

    private function hasEditRights($userToEdit){
        $hasEditRights = false;
        if($this->isSessionSet()){
            if($this->isAdmin() || $_SESSION['username'] == $userToEdit){
                $hasEditRights = true;
            }else{
                $this->echoJsonResponse(INVALID_USER_RIGHTS, BAD_REQUEST_ERROR_CODE);
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
        if(!$this->isPropertyInObject($object, 'username') || !$this->isPropertyInObject($object, 'position') || !$this->isPropertyInObject($object, 'password')){
            return;
        }

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
            return $this->echoJsonResponse(INSERT_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
        }
        else{
            return $this->echoJsonResponse(INSERT_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_CODE);
        }
    }
}