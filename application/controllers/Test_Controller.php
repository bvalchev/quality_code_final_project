<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 3.3.2020 г.
 * Time: 13:55
 */

class Test_Controller extends Basic_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    public function setDefaultMethod(){
        $headers = "From: valchev.boian@gmail.com \r\n".
                    "Reply-To: valchev.boian@gmail.com \r\n" .
                    'X-Mailer: PHP/' . phpversion();
                    @mail('valchev.boyan3@gmail.com', 'Test',  'Test', $headers);  
                    echo '<div>GJ</div>';
    }

    public function setFrameworkEmail()
    {
        $this->email->from('your@example.com', 'Your Name');
        $this->email->to('valchev.boyan3@gmail.com');
        $this->email->cc('another@another-example.com');
        $this->email->bcc('them@their-example.com');

        $this->email->subject('Email Test');
        $this->email->message('Testing the email class.');

        $this->email->send();
    }

    public function tryGet(){
        echo (file_get_contents('http://json.peakview.bg/b2b_programi_list_ekskurzii.php?us=APIUSER&ps=APIKEY&Turoperator_ID=11'));
    }

    public function try(){
        echo (file_get_contents('http://json.peakview.bg/b2b_programi_list_countries.php?us=e35232bd48c2e3e80eee63ebb0aee9a7o40Qjze9Ri&ps=JBPmBtdkFZxPW72e35232bd48c2e3e80eee63ebb0aee9a7'));
    }
}