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
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        // if($method == "OPTIONS") {
        //     die();
        // }
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->helper('date');
        $this->load->library('form_validation');
        $this->load->library('encryption');
        $this->load->model('Basic_Model');
        $this->load->helper('url', 'form', 'file');

        $this->offersTableToUpdate = $this->Basic_Model->getOffersTableToUpdate();
        $this->offersTableToRead = $this->Basic_Model->getOffersTableToUpdate();
        $this->datesForOffersTableToUpdate = $this->Basic_Model->getDatesForOffersTableToUpdate();
        $this->datesForOffersTableToRead = $this->Basic_Model->getDatesForOffersTableToRead();
        $this->hotelsForOffersTableToUpdate = $this->Basic_Model->getHotelsForOffersTableToUpdate();
        $this->hotelsForOffersTableToRead = $this->Basic_Model->getHotelsForOffersTableToRead();
    }

    protected $useSession = true;
    protected $settingsTable = 'settings';
    protected $badRequestErrorCode = 403;
    protected $unsuccessfulRequestErrorCode= 404;
    protected $successfulRequestCode = 200;
    protected $offersTableToUpdate;
    protected $offersTableToRead;
    protected $datesForOffersTableToUpdate;
    protected $datesForOffersTableToRead;
    protected $hotelsForOffersTableToUpdate;
    protected $hotelsForOffersTableToRead;

    protected function basicGetOperation(callable $getFunction, $keysForLikeOperatorInGetRequest, $table, callable $isSpecialKeyFunction, $isUsingSpecialKeys = false){
        // if(!isset($_SESSION['id']) && $this->useSession){
        //     $this->echoJsonResponse("Session not set", $this->unsuccessfulRequestErrorCode);
        //     return;
        // }

        $offset = 0;
        $limit = 50;
        $sortField = null;
        $sortOrder = 'ASC';
        $get = array();
        $arrayForLikeOperator = array();
        foreach ( $_GET as $key => $value ) {
            if ($key == 'offset') {
                $offset = $this->input->get($key);
            } else if ($key == 'limit') {
                $limit = $this->input->get($key);
            }else if($key == 'sortField'){
                if($this->db->field_exists($this->input->get($key), $table)){
                    $sortField = $this->input->get($key);
                } 
            }else if($key == 'sortOrder'){
                $sortOrder = $this->input->get($key);
            }else if(in_array($key, $keysForLikeOperatorInGetRequest)) {
                $arrayForLikeOperator[$key] = $this->input->get($key);
            }else if($isUsingSpecialKeys){
                if(call_user_func_array ($isSpecialKeyFunction, array($key)))
                {
                    $get[$key] = $this->input->get($key);
                }
            }else if($this->db->field_exists($key, $table)){
                $inputValue = $this->input->get($key);
                $valueToAssign = $inputValue;
                if(strtoupper($inputValue) == 'TRUE'){
                    $valueToAssign = true;
                }else if(strtoupper($inputValue) == 'FALSE'){
                    $valueToAssign = false;
                }
                $get[$key] = $valueToAssign;
            } else{
                $this->echoJsonResponse("The key ".$key." was not found", $this->badRequestErrorCode);
                return;
            }
        }
        $result = call_user_func_array ($getFunction, array($get, $arrayForLikeOperator, $limit, $offset, $sortField, $sortOrder));
        //$result = $model->getFunction($get, $arrayForLikeOperator, $limit, $offset, $sortField, $sortOrder);

        if(!$result) {
            $this->echoJsonResponse("Error occured", $this->unsuccessfulRequestErrorCode);
            return;
        }else{
            //$json = $result;//json_encode($result);
            echo $result;
        }
    }

    /**
     * The following function checks if a given object is JSON
     * @param $object - the object for which the check should be performed
     */
    protected function isJson($object) {
        json_decode($object);
        if(json_last_error() == JSON_ERROR_NONE){
            $isJson = true;
        }else{
            $this->echoJsonResponse("Data must be in json format", $this->badRequestErrorCode);
            $isJson = false;
        };
        return $isJson;
    }

    protected function echoJsonResponse($response, $code){
        http_response_code($code);
        $responseArray = array('response' => $response, 'code' => $code);
        echo json_encode($responseArray);
}

    protected function isDesiredMethodUsed($methodType){
        $isDesiredMethodUsed = true;
        if($this->input->method() != $methodType){
            $this->echoJsonResponse(strtoupper($methodType)." method should be used", $this->badRequestErrorCode);
            $isDesiredMethodUsed = false;
        }
        return $isDesiredMethodUsed;
    }

    protected function sanitizeInput($inputObject, $isArray = false){
        $sanitizedObject = $inputObject;

        foreach ($inputObject as $key => $value) {
            $valueToAssign = $value;
            $type = gettype($value);
            if($type == 'string'){
                $valueToAssign = htmlspecialchars($value, ENT_QUOTES);
            }
            if($type == 'integer'){
                $valueToAssign = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            }
            if($type == 'double'){
                $valueToAssign = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
            }
            
            if($isArray){
                $sanitizedObject[$key] = $valueToAssign;
            }else{
                $sanitizedObject->$key = $valueToAssign;
            } 
        }
        return $sanitizedObject;
    }

    protected function checkIfPropertyIsSet($array, $property){
        $result = true;
        if(empty($array) || !isset($array[$property]) ){
            $this->echoJsonResponse($property." is not provided or is incorrect", $this->badRequestErrorCode);
            $result = false;
        }
        return $result;
    }

    protected function checkAllKeysExist($dataArray, $table){
        $result = true;
        foreach ($dataArray as $key => $value) {
            if(!$this->db->field_exists($key, $table)){
                $this->echoJsonResponse("The key ".$key." was not found", $this->badRequestErrorCode);
                $result = false;
                return $result;
            }
        }
        return $result;
    }

     protected function executeInsertOperation($modelName, $insertFunctionName, $insertTupleData, $sendResponse = true){
        if($sendResponse){
            if (!$modelName->$insertFunctionName($insertTupleData)) {
                $this->echoJsonResponse("Insert operation failed", $this->unsuccessfulRequestErrorCode);
                return;
            }
            else{
                $this->echoJsonResponse("Insert operation successful", $this->successfulRequestCode);
                return;
            }
        }else{
            $modelName->$insertFunctionName($insertTupleData);
        }
        
    }

    protected function executeUpdateOperation($modelName, $updateFunctionName, $rowId, $updateTupleData){
        if (!$modelName->$updateFunctionName($rowId, $updateTupleData)) {
            $this->echoJsonResponse("Update operation failed", $this->unsuccessfulRequestErrorCode);
            return;
        }
        else{
            $this->echoJsonResponse("Update operation successful", $this->successfulRequestCode);
            return;
        }
    }

    public function upload_action($uploadPath, $fileTypes, $fileName, $useOptimization = false){
        set_time_limit(0);
        $config['upload_path'] = $uploadPath;
        $conig['max_size'] = '8192';
        $config['allowed_types'] = $fileTypes; 
        $config['file_name']= uniqid("", true) . '.' .pathinfo($_FILES[$fileName]['name'], PATHINFO_EXTENSION);
    //     
        $this->load->library('upload', $config);

       // $this->upload->initialize($config);
        if (  !$this->upload->do_upload($fileName))
        {
            $this->echoJsonResponse($this->upload->display_errors(), $this->unsuccessfulRequestErrorCode);
            return;
        }
        else
        {
            $uploadData = $this->upload->data();
            $picture =$uploadData['file_name'];
            if($useOptimization){
                $this->insertPictureForOptimization($uploadPath, $picture);
                //$this->optimize_action($uploadPath, $picture);
            }
            

            return $picture;
            /*if($insertUserData){
                $this->echoJsonResponse("Upload operation successful", $this->successfulRequestCode);
                return;
            }
            else{
                $this->echoJsonResponse("File upload failed", $this->unsuccessfulRequestErrorCode); 
                return;
            }*/
        }
    }

    public function insertPictureForOptimization($folderPath, $pictureName){
        $pictureData = array();
        $pictureData['folderPath'] = $folderPath;
        $pictureData['pictureName'] = $pictureName;
        $showResponse = false;
        $this->executeInsertOperation($this->Basic_Model, 'insertPictureForOptimization', $pictureData, $showResponse);

    }

    public function optimize_action($folderPath, $pictureName){
//        $jsonObject = file_get_contents('php://input', true);
//        if($this->isJson($jsonObject)) {
//            $decodedObject = json_decode($jsonObject);
//        }else{
    //        $this->echoJsonResponse("JSON object expected", $this->unsuccessfulRequestErrorCode);
    //        return;
//        }
        $path = $folderPath .'/' . $pictureName;
        $mime = mime_content_type($path);
        $info = pathinfo($path);
        $name = $info['basename'];
        $output = new CURLFile($path, $mime, $name);
        $data = array(
            "files" => $output,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=80');
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }
        curl_close ($ch);

        $decodedResponse = json_decode(($result));
        $ch = curl_init($decodedResponse->dest);
        $fp = fopen($path, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    // function definition to convert array to xml
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

    public function convertJsonToXml($json){
        //If the object is json, decode it, else print an error
        if($this->isJson($json)){
            $array_data = json_decode($json);
        }else{
            return;
        }

        // creating object of SimpleXMLElement
        $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');

        // function call to convert array to xml
        $this->array_to_xml($array_data, $xml_data);

        //saving generated xml file;
        //$result = $xml_data->asXML('/file/path/name.xml');
        return $xml_data;
    }

    protected function isValidEmail($email){
        $result = false;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result = true;
        }
        if(!$result){
            $this->echoJsonResponse("Email is not valid!", $this->badRequestErrorCode);
            return;
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