<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 3.3.2020 г.
 * Time: 13:55
 */

class Email_Controller extends Basic_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

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
        if(!$this->isDesiredMethodUsed('post')){
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

        $this->email->from($emailConfigData['emailFrom'], $emailConfigData['senderName']);
        $this->email->to(COMPANY_EMAIL_ADDRESS);
        $this->email->subject($emailConfigData['subject']);
        $this->email->message($emailConfigData['message']);
        if(!$this->email->send()){
            $this->echoJsonResponse(EMAIL_ERROR_MESSAGE, BAD_REQUEST_ERROR_CODE);
            return;
        }
        $this->echoJsonResponse(EMAIL_SENT_MESSAGE, SUCCESSFUL_REQUEST_CODE);
    }

    private function isCompleteEmailConfigurationProvided($emailConfigData){
        return ($this->isPropertyInArray($emailConfigData, "emailFrom") && $this->isPropertyInArray($emailConfigData, "senderName") &&
                $this->isPropertyInArray($emailConfigData, "subject") && $this->isPropertyInArray($emailConfigData, "message"));
    }
}