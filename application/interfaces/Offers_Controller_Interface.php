<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 18.3.2020 г.
 * Time: 16:31
 */

 /**
 * Interface Offers_Controller_Interface
 * The Interface is implemented by the Offers_Controller class
 */
interface Offers_Controller_Interface{
    /**
     * Offers_Controller constructor.
     */
    public function __construct();


    /**
     * The following function checks if the key should be considered for the offers filtering
     * @param $key - string - The current key to be checked
     * @return bool - true if the key is special
     */
    public function isSpecialKey($key);


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
    public function getOffers();


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
    public function getDetailsForOffer();
}