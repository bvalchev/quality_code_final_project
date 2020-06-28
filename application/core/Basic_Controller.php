<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Author: Boyan Valchev
 * Date: 24.3.2019 г.
 * Time: 22:51
 */

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Class Basic_Controller
 * The following class will be inherited by every other controller class
 * @Author Boyan Valchev
 */
class Basic_Controller extends CI_Controller
{
    
    public function __construct()
    {
        parent::__construct();
        $this->loadHeaders();
        $this->loadDependencies();
        $this->initializeTableNames();
    }
   
    protected $updateOffersTable;
    protected $readOffersTable;

    protected $updateDatesForOffersTable;
    protected $readDatesForOffersTable;

    protected $updateHotelsForOffersTable;
    protected $readHotelsForOffersTable;


    protected function executeGetRequest(callable $handlerFunction, $parametersArray){
        $result = call_user_func_array ($handlerFunction, $parametersArray);
        
    }

    /**
     * The following function checks if a given object is JSON
     * @param $object - the object for which the check should be performed
     */
    protected function isJson($object) {
        $isJson = true;
        json_decode($object);
        if(!json_last_error() == JSON_ERROR_NONE){
            $this->echoJsonResponse(NOT_JSON_ERROR, BAD_REQUEST_ERROR_CODE);
            $isJson = false;
        };
        return $isJson;
    }

    protected function echoJsonResponse($response, $code){
        http_response_code($code);
        $responseArray = array('response' => $response, 'code' => $code);
        echo json_encode($responseArray);
        return json_encode($responseArray);
}

    protected function isDesiredMethodUsed($methodType){
        $isDesiredMethodUsed = true;
        if($this->input->method() != $methodType){
            $this->echoJsonResponse(strtoupper($methodType).METHOD_CHECK_SUFFIX_MESSAGE, BAD_REQUEST_ERROR_CODE);
            $isDesiredMethodUsed = false;
        }
        return $isDesiredMethodUsed;
    }

    //TO DO: Refactor this
    protected function sanitizeInput($inputObject){
        $sanitizedObject = $inputObject;

        foreach ($inputObject as $key => $value) {
            $valueToAssign = $value;
            $type = gettype($value);
            if($type == 'string'){
                $valueToAssign = htmlspecialchars($value, ENT_QUOTES);
            }else if($type == 'integer'){
                $valueToAssign = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            }else if($type == 'double'){
                $valueToAssign = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
            }
            
            $sanitizedObject->$key = $valueToAssign;
        }
        return $sanitizedObject;
    }

    protected function isPropertyInArray($array, $property){
        $result = true;
        if(empty($array) || !isset($array[$property]) ){
            $this->echoJsonResponse($property.PROPERTY_NOT_SET_SUFFIX_MESSAGE, BAD_REQUEST_ERROR_CODE);
            $result = false;
        }
        return $result;
    }

    protected function isPropertyInObject($object, $property){
        $result = true;
        if(!isset($object->$property) ){
            $this->echoJsonResponse($property.PROPERTY_NOT_SET_SUFFIX_MESSAGE, BAD_REQUEST_ERROR_CODE);
            $result = false;
        }
        return $result;
    }

    protected function areAllKeysExisting($dataArray, $table){
        $result = true;
        foreach ($dataArray as $key => $value) {
            if(!$this->db->field_exists($key, $table)){
                $this->echoJsonResponse("The key ".$key." was not found", BAD_REQUEST_ERROR_CODE);
                $result = false;
                return $result;
            }
        }
        return $result;
    }

     protected function executeInsertOperation(callable $insertHandlerFunction, $parametersArray){
        if (call_user_func_array ($insertHandlerFunction, $parametersArray)) {
            $this->echoJsonResponse(INSERT_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_ERROR_CODE);
        }
        else{
            $this->echoJsonResponse(INSERT_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
        } 
    }

    protected function executeUpdateOperation($updateHandlerFunction, $rowId, $updateTupleData){
        $parametersArray = array($rowId, $updateTupleData);
        if (call_user_func_array ($updateHandlerFunction, $parametersArray)) {
            $this->echoJsonResponse(UPDATE_SUCCESSFUL_MESSAGE, SUCCESSFUL_REQUEST_ERROR_CODE);        
        }
        else{
            $this->echoJsonResponse(UPDATE_FAILED_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
        }
    }

    protected function uploadFile($uploadPath, $allowedFileTypes, $fileName){
        $this->buildUploadConfig($uploadPath, $allowedFileTypes, $fileName);
        if ($this->upload->do_upload($fileName)){
            $uploadData = $this->upload->data();
            $picture =$uploadData['file_name'];    
            return $picture;
        }
        else{
            $this->echoJsonResponse($this->upload->display_errors(), UNSUCCESSFUL_REQUEST_ERROR_CODE);
            return;
        }
    }

    protected function convertJsonToXml($json){
        if(!$this->isJson($json)){
            return;    
        }

        $array_data = json_decode($json);
        $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        $this->array_to_xml($array_data, $xml_data);
        $result = $xml_data->asXML(XML_SAVE_PATH);
        //return $xml_data;
    }

    protected function isValidEmail($email){
        $result = false;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result = true;
        }
        if(!$result){
            $this->echoJsonResponse(EMAIL_NOT_VALID_MESSAGE, BAD_REQUEST_ERROR_CODE);
            return;
        }
    }

    protected function isSessionSet(){
        $result = true;
        if(!isset($_SESSION['id']) && USE_SESSION){
            $this->echoJsonResponse(SESSION_NOT_SET_MESSAGE, UNSUCCESSFUL_REQUEST_ERROR_CODE);
            $result = false;
        }
        return $result;
    }

    private function loadHeaders(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }

    private function loadDependencies(){
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->helper('date');
        $this->load->library('form_validation');
        $this->load->library('encryption');
        $this->load->model('Basic_Model');
        $this->load->helper('url', 'form', 'file');
    }

    private function initializeTableNames(){
        $tableNames = $this->Basic_Model->getTableNames();
        $this->updateOffersTable = $tableNames['updateOffersTable'];
        $this->readOffersTable = $tableNames['readOffersTable'];
        $this->updateDatesForOffersTable = $tableNames['updateDatesForOffersTable'];
        $this->readDatesForOffersTable = $tableNames['readDatesForOffersTable'];
        $this->updateHotelsForOffersTable = $tableNames['updateHotelsForOffersTable'];
        $this->readHotelsForOffersTable = $tableNames['readHotelsForOffersTable'];
    }

    private function buildUploadConfig($uploadPath, $allowedFileTypes, $fileName){
        $config['upload_path'] = $uploadPath;
        $conig['max_size'] = '8192';
        $config['allowed_types'] = $fileTypes; 
        $config['file_name']= uniqid("", true) . '.' .pathinfo($_FILES[$fileName]['name'], PATHINFO_EXTENSION);    
        $this->load->library('upload', $config);
    }

    private function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    /*protected function validateInput($object){
        $this->form_validation->set_data(array(
            'name'    =>  'Full Name',
            'city'    =>  'City',
        ));

        $this->form_validation->set_rules('name', 'Name', 'trim|required|');
        $this->form_validation->set_rules('city', 'City', 'trim|required');
        $this->form_validation->set_rules('age', 'Age', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
        }
    }*/

}
?>