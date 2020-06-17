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
require_once APPPATH.'interfaces/Offers_Controller_Interface.php';

/**
 * Class Offers_Controller
 * The following class inherits Basic_Controller class and is responsible for the handling of offers operations
 * @Author Boyan Valchev
 */

class Offers_Controller extends Basic_Controller implements Offers_Controller_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
	$this->load->helper('url');
        $this->load->model('Offers_Model');
    }
    private $keysForLikeOperatorInGetRequest = array();

    /**
     * The following function checks if the key should be considered for the offers filtering
     * The function is not supposed to be called from the api. Thats why it is not routed.
     * @param $key - string - The current key to be checked
     * @return bool - true if the key is special
     */
    public function isSpecialKey($key){
        return (($key == 'startDate') || ($key == 'endDate') || ($key == 'maxPrice') || ($key == 'minPrice') || 
                ($key == 'countries') ||($key == 'cities') || ($key == 'afterDate') || ($key == 'beforeDate') || 
                ($key == 'isHoliday') || ($key == 'isExotic') || ($key == 'isEarly') || ($key == 'getNewest') ||
                ($key == 'transport'));
    }

    /**
     * The following function is responsible for getting offers from the server
     * Example url: www.websitename.com/offers
     * The function considers the following parameters from URL:
     *      startDate - The date (in format dd-mm-yyyy) the offer should START from
     *      endDate - The date (in format dd-mm-yyyy) the offer should END at
     *      afterDate - The date (in format dd-mm-yyyy) value and all the dates after it on which offers START
     *      beforeDate - The date (in format dd-mm-yyyy) value and all the dates before it on which offers should END
     *      countries - Expects a trimmed string, containing the countries to filter on, separated by commas
     *      cities - Expects a trimmed string, containing the cities to filter on, separated by commas
     *      maxPrice - The value of this parameter should be the max price to filter on
     *      minPrice - The value of this parameter should be the minimum price to filter on
     *      isHoliday - When the value is true, only offers for holidays will be returned
     *      isExotic - Checks if coutry is exotic - not in Europe
     *      isEarly - Checks if the offers start date is at least 5 months from now
     *      getNewest - Sorts the offers according to the date from the third party
     *      offset - The value of the offer offset
     *      limit - The value of the offer count limit
     *      sortField - The value of the field to sort on
     *      sortOrder - Expects ASC or DESC depending on the way the items to be sorted
     * @return - JSON object, containing all of the offers that match the filtering parameters
     */
    public function getOffers(){
        //if(!isset($_SESSION['id']) && $this->useSession){
        //    $this->echoJsonResponse("Session not set", $this->unsuccessfulRequestErrorCode);
        //    return;
        //}
        if(!parent::isDesiredMethodUsed('get')){
            return;
        };
        
        echo parent::basicGetOperation(array($this->Offers_Model, 'getOperation'),  $this->keysForLikeOperatorInGetRequest, $this->offersTableToUpdate, array($this, 'isSpecialKey'), true);
    }
    /**
     * The following function gets additional details for offer.
     * Under additional information is considered:
     *      Transport type
     *      Description
     *      Clean description
     *      Price includes
     *      Price does not include
     *      The actual offer as file
     *      Additional photos
     *      Dates JSONs 
     *      Hotels JSONs
     *      And the date the offer was last changed from provider
     * The function needs the offer PID and isHoliday to get additional details for from the URL
     * Example: baseurl/index.php/Offers_Controller/getDetailsForOffer?pid=25&isHoliday=true
     * @return echos the data as JSON;
     */
    public function getDetailsForOffer(){
        //if(!isset($_SESSION['id']) && $this->useSession){
        //   $this->echoJsonResponse("Session not set", $this->unsuccessfulRequestErrorCode);
        //    return;
        //}
        $pid = -1;
        $isHoliday = false;
        if(array_key_exists('pid', $_GET))
        {
            $pid = $_GET['pid'];
        }
        if($pid == -1){
            $this->echoJsonResponse("Pid not provided", $this->badRequestErrorCode);
            return;
        }

        if(!array_key_exists('isHoliday', $_GET)){
            $this->echoJsonResponse("isHoliday is not provided", $this->badRequestErrorCode);
            return;
        }else{
            if($_GET['isHoliday']== 'true'){
                $isHoliday = true;
            }else if($_GET['isHoliday']== 'false'){
                $isHoliday = false;
            }
        }
        $fullData = $this->Offers_Model->getDetailsForOffer($pid, $isHoliday);
        echo $fullData;
    }

    public function getDistinctOfferCountries(){
        echo $this->Offers_Model->getDistinctOfferCountries();
    }

    public function getStaticOfferView(){
	$separatedArrays = explode('_', $_GET['data']);
	$pid = $separatedArrays[0];
	$isHolidayString = $separatedArrays[1];
        if($isHolidayString== 'true'){
            $isHoliday = true;
        }else if($sHolidayString== 'false'){
            $isHoliday = false;
        }
        
        $dataArray = json_decode($this->Offers_Model->getDetailsForOffer($pid, $isHoliday), true)[0];
        $this->load->view('staticView', $dataArray);
    }

     /**
     * The following function gets additional details for hotel.
     * The function needs the offer PID and hotelId to get additional details for from the URL
     * Example: baseurl/index.php/hotels?pid=25&hotelId=32
     * @return echos the data as JSON;
     */
    public function getHotelInfo(){
        $pid = -1;
        if(array_key_exists('pid', $_GET)){
            $pid = $_GET['pid'];
        }else{
            $this->echoJsonResponse("Pid not provided", $this->badRequestErrorCode);
            return;
        }

        $hotelId = -1;
        if(array_key_exists('hotelId', $_GET)){
            $hotelId = $_GET['hotelId'];
        }else{
            $this->echoJsonResponse("Hotel ID is not provided", $this->badRequestErrorCode);
            return;
        }

        
        $getHotelInfoUrl = "http://json.peakview.bg/b2b_programa_hotel.php?" .
                            "PID=" . 
                            $pid  . 
                            "&H=" .
                            $hotelId .
                            "&us=e35232bd48c2e3e80eee63ebb0aee9a7o40Qjze9Ri&ps=JBPmBtdkFZxPW72e35232bd48c2e3e80eee63ebb0aee9a7";
        $offerInfoJson = file_get_contents($getHotelInfoUrl);
        $offerInfoArray = json_decode($offerInfoJson, true);
        echo json_encode($offerInfoArray[0]['hotelinfo'], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}