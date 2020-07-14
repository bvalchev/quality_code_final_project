<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 30.4.2019 г.
 * Time: 19:44
 */

/**
 * Interface Authentication_Controller_Interface
 * The Interface is implemented by the Authentication_Controller class
 */
interface Authentication_Controller_Interface{


    /**
     * Authentication_Controller constructor.
     */
    public function __construct();

    /**
     * The following function is responsible for login
     * Example url to access the function www.websitename.com/index.php/login
     * It requires JSON object POST parameter, containing the following information
     * JSON object Parameter 1: username
     * JSON object Parameter 2: password
     * The function sets the session token
     */
    public function login();


    /**
     * The following function is responsible for user insertion
     * Example url to access the function www.websitename.com/index.php/user/register
     * It requires JSON object POST parameter, containing the following information
     * JSON object Parameter 1: username
     * JSON object Parameter 2: password
     * JSON object Parameter 3: position
     * The function inserts the user into the database
     */
    public function insertUser();


    /**
     * The following function is responsible for password update
     * Example url to access the function www.websitename.com/index.php/password/update
     * It requires JSON object as POST parameter, containing the following information
     * JSON object Parameter 1: username
     * JSON object Parameter 2: password
     * JSON object Parameter 3: newPassword
     */
    public function updatePassword();


    /**
     * The following function is responsible for deleting a user
     * Example url to access the function www.websitename.com/index.php/user/delete
     * It requires JSON object as DELETE request parameter, containing the following information
     * JSON object Parameter 1: username
     */
    public function deleteUser();


    /**
     * The function checks if the user is logged in and if so returns the session data
     * in JSON format
     * Example url to access the function www.websitename.com/index.php/session
     * @return $jsonEncodedResponse - json with session data
     */
    public function getSessionDetails();


    /**
     * The following function is responsible for destroying the session
     * Example url to access the function www.websitename.com/index.php/logout
     * It unsets the session token.
     */
    public function logout();
}