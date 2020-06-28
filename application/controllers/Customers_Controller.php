<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 3.3.2020 г.
 * Time: 13:55
 */


/**
 * Include the interface from the interface folder
 */
require_once APPPATH.'interfaces/Customers_Controller_Interface.php';

class Customers_Controller extends Basic_Controller implements Customers_Controller_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    private $emailTo = "office@aratour.bg";

    // public function setDefaultMethod($emailFrom){
    //     $headers = "From: valchev.boian@gmail.com \r\n".
    //                 "Reply-To: valchev.boian@gmail.com \r\n" .
    //                 'X-Mailer: PHP/' . phpversion();
    //                 @mail('valchev.boyan3@gmail.com', 'Test',  'Test', $headers);  
    //                 echo '<div>GJ</div>';
    // }

    /**
     * The following function is responsible for sending emails
     * Example URL for accessing: www.examplewebsitename.com/email
     * The function REQUIRES a POST method.
     * The function expects the following parameters as JSON from post body:
     *      emailFrom - string - the email of the client
     *      senderName - string - the name of the client
     *      subject - string - the subject of the email
     *      message - text - the client's message.
     */
    public function sendEmail()
    {
        if(!parent::isDesiredMethodUsed('post')){
            return;
        };
       
        $dataObject = file_get_contents('php://input', true);
        
        if($this->isJson($dataObject)){
            $emailConfigData = json_decode($dataObject, true);
        }else{
            return;
        }
 	
        if(!$this->isCompleteEmailConfigurationProvided($emailConfigData)){
            return;
        }

        //if(!$this->isValidEmail($emailConfigData['emailFrom'])){
        //    return;
        //}
        $this->email->from($emailConfigData['emailFrom'], $emailConfigData['senderName']);
        $this->email->to($this->emailTo);
        $this->email->subject($emailConfigData['subject']);
        $this->email->message($emailConfigData['message']);
        if(!$this->email->send()){
            $this->echoJsonResponse("Email sending failed", $this->badRequestErrorCode);
            return;
        }
        $this->echoJsonResponse("Email sent!", $this->successfulRequestCode);
    }

    private function isCompleteEmailConfigurationProvided($emailConfigData){
        return ($this->isPropertyInArray($emailConfigData, "emailFrom") && $this->isPropertyInArray($emailConfigData, "senderName") &&
                $this->isPropertyInArray($emailConfigData, "subject") && $this->isPropertyInArray($emailConfigData, "message"));
    }

    public function try(){
		$data = array();
		$data['username'] = 'admin';
		$data['password'] = 'admin';
		$encodedData = json_encode($data);
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://tuni.terminalbulgaria.com/index.php/login');
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
		$result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }
		curl_close ($ch);
		var_dump($result);
		die();
	}
}