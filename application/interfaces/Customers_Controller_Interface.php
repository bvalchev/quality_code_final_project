<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 18.3.2020 г.
 * Time: 16:31
 */

 /**
 * Interface Customers_Controller_Interface
 * The Interface is implemented by the Customers_Controller class
 */
interface Customers_Controller_Interface{
    /**
     * Customers_Controller constructor.
     */
    public function __construct();

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
    public function sendEmail();
}

