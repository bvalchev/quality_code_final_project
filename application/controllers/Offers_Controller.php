<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 3.3.2020 г.
 * Time: 13:55
 */

/**
 * Class Offers_Controller
 * The following class inherits Basic_Controller class and is responsible for the handling of offers operations
 * @Author Boyan Valchev
 */

class Offers_Controller extends Basic_Controller
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
    public function getOffers($parameters = null, $method = null){
        if(!$this->isDesiredMethodUsed('get', $method)){
            return false;
        };

        $getParameters = $parameters;
        if(!$getParameters){
            $getParameters = $_GET;
        }
    
        // if(!$this->areAllKeysExisting($getParameters, $this->updateOffersTable)){
        //     return;
        // }        
        if(!$method){
            echo $this->Offers_Model->executeGetOperation($getParameters, $this->keysForLikeOperatorInGetRequest);
        }else{
            return $this->Offers_Model->executeGetOperation($getParameters, $this->keysForLikeOperatorInGetRequest);
        }
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
    public function getDetailsForOffer($method = null){
        if(!$this->isDesiredMethodUsed('get', $method)){
            return false;
        };
        if(!$this->isIdentificatorProvided($_GET)){
            return false;
        }
        $pid = $_GET['pid'];
        $isHoliday = $this->shouldSearchForHoliday($_GET);
        $fullData = $this->Offers_Model->getDetailsForOffer($pid, $isHoliday);
        if(!$method){
            echo $fullData;
        }else{
            return $fullData;
        }
    }

    public function getDistinctOfferCountries($method = null){
        if(!$this->isDesiredMethodUsed('get', $method)){
            return false;
        };
        if(!$method){
            echo $this->Offers_Model->getDistinctOfferCountries();
        }else{
            return $this->Offers_Model->getDistinctOfferCountries();
        }
    }

    public function getStaticOfferView(){
        $separatedArrays = explode('_', $_GET['data']);
        $pid = $separatedArrays[0];
        $isHoliday = (strtoupper($separatedArrays[1]) === 'FALSE');    
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
        if(!$this->isIdentificatorProvided($_GET) || !$this->isHotelIdProvided($_GET)){
            return;
        }
        $pid = $_GET['pid'];
        $hotelId = $_GET['hotelId'];
        $getHotelInfoUrl = $this->getHotelInfoUrl($pid, $hotelId);
        $offerInfoJson = file_get_contents($getHotelInfoUrl);
        $offerInfoArray = json_decode($offerInfoJson, true);
        echo json_encode($offerInfoArray[0]['hotelinfo'], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }

    private function getIdentificator($parameters){
        $isIdentificatorExisting = array_key_exists('pid', $parameters);
        if(!$isIdentificatorExisting){
            $this->echoJsonResponse(PID_NOT_PROVIDED_MESSAGE, BAD_REQUEST_ERROR_CODE);
        }
        return $isIdentificatorExisting;
    }

    private function shouldSearchForHoliday($parameters){
        $result = true;
        if( !array_key_exists('isHoliday', $parameters) || strtoupper($parameters) == 'FALSE'){
            $result = false;
        }
        return $result;
    }

    private function isHotelIdProvided($parameters){
        $isHotelIdProvided = array_key_exists('hotelId', $parameters);
        if(!$isHotelIdProvided){
            $this->echoJsonResponse(HOTELID_NOT_PROVIDED_MESSAGE, BAD_REQUEST_ERROR_CODE);
        }
        return $isHotelIdProvided;
    }

    private function isIdentificatorProvided($parameters){
        $isPidProvided = array_key_exists('pid', $parameters);
        if(!$isPidProvided){
            $this->echoJsonResponse(PID_NOT_PROVIDED_MESSAGE, BAD_REQUEST_ERROR_CODE);
        }
        return $isPidProvided;
    }

    private function getHotelInfoUrl($pid, $hotelId){
        return HOTEL_INFO_ENDPOINT .
            "PID=" . 
            $pid  . 
            "&H=" .
            $hotelId .
            AUTHENTICATION_KEY;
    }
}