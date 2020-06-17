<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 15.2.2020 г.
 * Time: 16:23
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Offer_Update_Model extends Basic_Model{
    

    /**
     * Overrides the method of the ancestor
     * Inserts a tuple in the clients table
     */

    private $production = true;
    private $datesFormat = 'Y-m-d';
    private $fetchAdditionalDetails = true;
    private $turoperatorsTable = 'turoperators';
    private $MAX_OFFERS_FOR_SINGLE_TUROPERATOR = 700;
    private $MAX_OFFERS_FOR_SINGLE_BATCH = 300;
    private $thirdPartyBaseUrl;
    private $thirdPartyCredentials;
    public $holidaysDetailsEndpointName = "b2b_programa_pochivka.php";
    public $excursionsDetailsEndpointName = "b2b_programa_ekskurzia.php";

    public function __construct()
    {
        parent::__construct();
        if($this->production){
            $this->thirdPartyBaseUrl = "http://json.peakview.bg/";
            $this->thirdPartyCredentials = "us=e35232bd48c2e3e80eee63ebb0aee9a7o40Qjze9Ri&ps=JBPmBtdkFZxPW72e35232bd48c2e3e80eee63ebb0aee9a7";
        
        }else{
            $this->thirdPartyBaseUrl = "http://demojson.peakview.bg/";
            $this->thirdPartyCredentials = "us=APIUSER&ps=APIKEY";
        }
    }

    private function truncateDynamicTables()
    {
        $this->db->empty_table($this->offersTableToUpdate);
        $this->db->empty_table($this->datesForOffersTableToUpdate);
        $this->db->empty_table($this->hotelsForOffersTableToUpdate);
    }

    public function updateTuroperatorsTable(){
        $turoperatorsArray = $this->getTuroperatorsArray();
        foreach($turoperatorsArray as $singleTuroperatorInfo){
            $insertData = array();
            $insertData['TUROPERATOR_ID']= $singleTuroperatorInfo['TUROPERATOR_ID'];
            $insertData['name']= $singleTuroperatorInfo['TUROPERATOR'];
            parent::insert($this->turoperatorsTable, $insertData);
        }
    }
    
    public function getTuroperatorsArray()
    {
        $getTuroperatorsUrl = $this->thirdPartyBaseUrl . "b2b_turoperator_spisak.php?" . $this->thirdPartyCredentials;
        $turoperatorsJson = file_get_contents($getTuroperatorsUrl);
        $turoperatorsArray = json_decode($turoperatorsJson, true);
        return $turoperatorsArray;
    }
    
    private function getInsertData($baseDataArray, $detailsArray, $fetchAdditionalDetails, $isHoliday)
    {
        $dataToInsert = array();
        $dataToInsert['isHoliday'] = $isHoliday ? 1 : 0;
        if(array_key_exists('PID', $baseDataArray)){
            $dataToInsert['pid'] = $baseDataArray['PID'];
        }
        if(array_key_exists('TUROPERATOR_ID', $baseDataArray)){
            $dataToInsert['turoperator_id'] = $baseDataArray['TUROPERATOR_ID'];
        }
        if(array_key_exists('TUROPERATOR', $baseDataArray)){
            $dataToInsert['turoperator_name'] = $baseDataArray['TUROPERATOR'];
        }
        if(array_key_exists('COUNTRY', $baseDataArray)){
            $dataToInsert['country'] = $baseDataArray['COUNTRY'];
        }
        if(array_key_exists('title', $baseDataArray)){
            $dataToInsert['title'] = $baseDataArray['title'];
        }
        if(array_key_exists('spoDetailsDates', $baseDataArray)){
            $dataToInsert['dates'] = $baseDataArray['spoDetailsDates'];
        }
        if(array_key_exists('MINPRICE', $baseDataArray)){
            $dataToInsert['min_price'] = $baseDataArray['MINPRICE'];
        }
        if(array_key_exists('tragvane_ot', $baseDataArray)){
            $dataToInsert['departure_place'] = $baseDataArray['tragvane_ot'];
        }
        if(array_key_exists('broj_dni', $baseDataArray)){
            $dataToInsert['number_of_days'] = $baseDataArray['broj_dni'];
        }
        if(array_key_exists('broj_noshtuvki', $baseDataArray)){
            $dataToInsert['number_of_nights'] = $baseDataArray['broj_noshtuvki'];
        }
        if(array_key_exists('IMG', $baseDataArray)){
            $dataToInsert['main_image'] = preg_replace("/^http:/i", "https:", $baseDataArray['IMG']);
        }
        if(array_key_exists('BIG_IMG', $baseDataArray)){
            $dataToInsert['main_image_big'] = preg_replace("/^http:/i", "https:", $baseDataArray['BIG_IMG']);
        }

        $dataToInsert['isDetailUpdated'] = false;

        if($fetchAdditionalDetails){
            $this->processAdditionalOfferData($detailsArray, $dataToInsert);
        }
      
        return $dataToInsert;
    }

    public function processAdditionalOfferData($detailsArray, &$resultArray){
        $resultArray['isDetailUpdated'] = true;
        if($detailsArray != null){
              // if(array_key_exists('POCHIVKA', $detailsArray)){
            //     $dataToInsert['isHoliday'] = $detailsArray['POCHIVKA'];
            // }
            
            
            if(array_key_exists('STATUS', $detailsArray)){
                $resultArray['isActive'] = $detailsArray['STATUS'];
            }
            if(array_key_exists('transport_text', $detailsArray)){
                $resultArray['transport_type'] = $detailsArray['transport_text'];
            }
            if(array_key_exists('valuta', $detailsArray)){
                $resultArray['currency'] = $detailsArray['valuta'];
            }
            if(array_key_exists('opisanie', $detailsArray)){
                $resultArray['description'] = $detailsArray['opisanie'];
            }
            if(array_key_exists('opisanie_clean', $detailsArray)){
                $resultArray['description_clean'] = $detailsArray['opisanie_clean'];
            }
            if(array_key_exists('CENATA_VKLYUCHVA', $detailsArray)){
                $resultArray['price_includes'] = $detailsArray['CENATA_VKLYUCHVA'];
            }
            if(array_key_exists('CENATA_NE_VKLYUCHVA', $detailsArray)){
                $resultArray['price_not_includes'] = $detailsArray['CENATA_NE_VKLYUCHVA'];
            }
            if(array_key_exists('oferta_file', $detailsArray)){
                $resultArray['file'] = $detailsArray['oferta_file'];
            }
            if(array_key_exists('IMG2', $detailsArray)){
                $resultArray['image2'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG2']);
            }
            if(array_key_exists('BIG_IMG2', $detailsArray)){
                $resultArray['image2_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG2']);
            }
            if(array_key_exists('IMG3', $detailsArray)){
                $resultArray['image3'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG3']);
            }
            if(array_key_exists('BIG_IMG3', $detailsArray)){
                $resultArray['image3_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG3']);
            }
            if(array_key_exists('IMG4', $detailsArray)){
                $resultArray['image4'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG4']);
            }
            if(array_key_exists('BIG_IMG4', $detailsArray)){
                $resultArray['image4_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG4']);
            }
            if(array_key_exists('IMG5', $detailsArray)){
                $resultArray['image5'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG5']);
            }
            if(array_key_exists('BIG_IMG5', $detailsArray)){
                $resultArray['image5_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG5']);
            }
            if(array_key_exists('IMG6', $detailsArray)){
                $resultArray['image6'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG6']);
            }
            if(array_key_exists('BIG_IMG6', $detailsArray)){
                $resultArray['image6_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG6']);
            }
            if(array_key_exists('IMG7', $detailsArray)){
                $resultArray['image7'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG7']);
            }
            if(array_key_exists('BIG_IMG7', $detailsArray)){
                $resultArray['image7_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG7']);
            }
            if(array_key_exists('IMG8', $detailsArray)){
                $resultArray['image8'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG8']);
            }
            if(array_key_exists('BIG_IMG8', $detailsArray)){
                $resultArray['image8_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG8']);
            }
            if(array_key_exists('IMG9', $detailsArray)){
                $resultArray['image9'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG9']);
            }
            if(array_key_exists('BIG_IMG9', $detailsArray)){
                $resultArray['image9_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG9']);
            }
            if(array_key_exists('IMG10', $detailsArray)){
                $resultArray['image10'] =  preg_replace("/^http:/i", "https:", $detailsArray['IMG10']);
            }
            if(array_key_exists('BIG_IMG10', $detailsArray)){
                $resultArray['image10_big'] =  preg_replace("/^http:/i", "https:", $detailsArray['BIG_IMG10']);
            }
            if(array_key_exists('UPDATEID', $detailsArray)){
                $resultArray['last_updated'] = $detailsArray['UPDATEID'];
            }
            if(array_key_exists('dates', $detailsArray)){
                $resultArray['dates_json'] = json_encode($detailsArray['dates']);
            }
            if(array_key_exists('hotelsdata', $detailsArray)){
                 $resultArray['hotelsdata_json'] = json_encode($detailsArray['hotelsdata']);
            }
              
        }
        
    }

    public function updateHotelsForOffers($hotelsForOffersJson, $pid, $table){
        $hotelsForOffersArray = json_decode($hotelsForOffersJson, true);
        if($hotelsForOffersArray != null)
        {
            foreach($hotelsForOffersArray as $singleTuple)
            {
                if(is_array($singleTuple)){
                    $hotelsDataToInsert = array();
                    $hotelsDataToInsert['offer_pid '] = $pid;
                    if(array_key_exists('hotel_id', $singleTuple)){
                        $hotelsDataToInsert['hotel_id'] = $singleTuple['hotel_id'];
                    }
                    if(array_key_exists('hotel_name', $singleTuple)){
                        $hotelsDataToInsert['hotel_name'] = $singleTuple['hotel_name'];
                    }
                    if(array_key_exists('hotel_place', $singleTuple)){
                        $hotelsDataToInsert['hotel_place'] = $singleTuple['hotel_place'];
                    }
                    if(array_key_exists('hotel_small_img', $singleTuple)){
                        $hotelsDataToInsert['hotel_image'] = $singleTuple['hotel_small_img'];
                    }
                    if(array_key_exists('hotel_big_img', $singleTuple)){
                        $hotelsDataToInsert['hotel_big_image'] = $singleTuple['hotel_big_img'];
                    }
                    if(array_key_exists('hotel_cena_ot', $singleTuple)){
                        $hotelsDataToInsert['hotel_min_price'] = $singleTuple['hotel_cena_ot'];
                    }
                    if(array_key_exists('hotel_valuta', $singleTuple)){
                        $hotelsDataToInsert['currency'] = $singleTuple['hotel_valuta'];
                    }
                    
                    parent::insert($this->hotelsForOffersTableToUpdate, $hotelsDataToInsert);
                }       
            }  
        }
    }

    private function switchReadAndUpdateTables()
    {
        $this->db->update($this->settingsTable, array('setting_value' => $this->offersTableToUpdate), array('setting_name' => 'offersTableToRead'));
        $this->db->update($this->settingsTable, array('setting_value' => $this->offersTableToRead), array('setting_name' => 'offersTableToUpdate'));
        $this->db->update($this->settingsTable, array('setting_value' => $this->datesForOffersTableToUpdate), array('setting_name' => 'datesForOffersTableToRead'));
        $this->db->update($this->settingsTable, array('setting_value' => $this->datesForOffersTableToRead), array('setting_name' => 'datesForOffersTableToUpdate'));
        $this->db->update($this->settingsTable, array('setting_value' => $this->hotelsForOffersTableToUpdate), array('setting_name' => 'hotelsForOffersTableToRead'));
        $this->db->update($this->settingsTable, array('setting_value' => $this->hotelsForOffersTableToRead), array('setting_name' => 'hotelsForOffersTableToUpdate'));
    }

    public function getAdditionalDetailsArray($fetchAdditionalDetails, $offerPID, $offerDetailsEnpointName)
    {
        $offerDetailsArray = array();
        if($fetchAdditionalDetails){
           
            $offerDetailsInfoUrl =$this->thirdPartyBaseUrl.
                                $offerDetailsEnpointName.
                                "?PID=".
                                $offerPID.
                                "&". 
                                $this->thirdPartyCredentials;
            //$this->output->enable_profiler(TRUE);
            $offerDetailsJson = file_get_contents($offerDetailsInfoUrl);
            $offerDetailsArray = json_decode($offerDetailsJson, true);
            
            if(!empty($offerDetailsArray)){
                $offerDetailsArray = $offerDetailsArray[0];
            }
        }
        return $offerDetailsArray;
    }

    private function insertIntoOffersTable($offerBaseInfoUrl, $offerDetailsEnpointName, $isHoliday = false)
    {
        set_time_limit(0);
        $offersForTuroperatorJson = file_get_contents($offerBaseInfoUrl);
        $offersForTuropatorArray = json_decode($offersForTuroperatorJson, true);
        $fetchAdditionalDetails = $this->fetchAdditionalDetails;
        
        if($offersForTuropatorArray == null){
            return;
        }
        foreach($offersForTuropatorArray as $singleOffer)
        {   
            $offerDetailsArray = $this->getAdditionalDetailsArray($fetchAdditionalDetails, $singleOffer["PID"], $offerDetailsEnpointName);
            $dataToInsert = $this->getInsertData($singleOffer, $offerDetailsArray, $fetchAdditionalDetails, $isHoliday);
            parent::insert($this->offersTableToUpdate, $dataToInsert);
        }
    }

    public function updateDates($pid, $dates, $daysToAdd, $isDatesJsonAvailable, $isHoliday, $table){
        set_time_limit(0);
        $datesArray = json_decode($dates, true);
        if($isDatesJsonAvailable && $datesArray == null)
        {
            return;
        }

        if(!$isDatesJsonAvailable && strpos($dates, ',') !== false ) 
        {
            $datesArray = explode(',', $dates);
        }
        
        foreach($datesArray as $singleTuple)
        {
            $dataToInsert = $this->getDatesDataToInsert($pid, $singleTuple, $daysToAdd, $isHoliday, $isDatesJsonAvailable);
            parent::insert($table, $dataToInsert);
        }
    }


    private function getDatesDataToInsert($pid, $singleTuple, $daysToAdd, $isHoliday, $fetchAdditionalDetails)
    {
        $dataToInsert = array();
        $dataToInsert['offer_pid'] = $pid;
        $dataToInsert['isHoliday'] = $isHoliday;
        if($fetchAdditionalDetails){
            $dataToInsert['date_start'] = date($this->datesFormat, strtotime($singleTuple['data']));
            $dataToInsert['date_min_price'] = $singleTuple['data_price'];
            $dataToInsert['date_currency'] = $singleTuple['data_valuta'];
	        $dataToInsert['date_end'] = date($this->datesFormat,strtotime($singleTuple['data'] . ' + '.$daysToAdd.' days'));
        }else{
            $dataToInsert['date_start'] = date($this->datesFormat, strtotime($singleTuple));
            $dataToInsert['date_end'] = date($this->datesFormat, strtotime($singleTuple . ' + '.$daysToAdd.' days'));
        }
        return $dataToInsert;
    }

    private function insertExcursions($turoperatorId/*, &$shouldStopCounter, &$offersInsertedCounter*/)
    {
        set_time_limit(0);
        $getExcursionsForTuroperatorUrl = $this->thirdPartyBaseUrl . 
                                        "b2b_programi_list_ekskurzii.php?".
                                        $this->thirdPartyCredentials .
                                        "&Turoperator_ID=" . 
                                        $turoperatorId; 
        $isHoliday = false;
        $this->insertIntoOffersTable($getExcursionsForTuroperatorUrl, $this->excursionsDetailsEndpointName/*, $shouldStopCounter*/, $isHoliday);
    }

    private function insertHolidays($turoperatorId/*, &$shouldStopCounter, &$offersInsertedCounter*/)
    {
        set_time_limit(0);
        $getHolidaysForTuroperatorUrl = $this->thirdPartyBaseUrl . 
                                        "b2b_programi_list_pochivki.php?".
                                        $this->thirdPartyCredentials .
                                        "&Turoperator_ID=" . 
                                        $turoperatorId;
        $isHoliday = true;
        $this->insertIntoOffersTable($getHolidaysForTuroperatorUrl, $this->holidaysDetailsEndpointName/*, $shouldStopCounter*/, $isHoliday);
    }

    private function shouldRunDetailedUpdate()
    {
        $lastTablesUpdateDateString = $this->getSettingsTable('lastTablesUpdateDate');
        return (date($this->datesFormat, strtotime($lastTablesUpdateDateString)) < date($this->datesFormat, now('Europe/Sofia')));
    }

    private function updateLastTablesUpdateDateSetting($valueToSet){
        $data = array(
            'setting_id' => 7,
            'setting_name' => 'lastTablesUpdateDate',
            'setting_value' => $valueToSet
        );
        $this->db->where('setting_name', 'lastTablesUpdateDate');
        $this->db->update($this->settingsTable, $data);
    }

    private function getOffersForDetailedUpdate(){
        $whereArray = array(
            'isDetailUpdated' => false
        );
        $offset = 0;
        $limit = $this->MAX_OFFERS_FOR_SINGLE_BATCH;
        $query = $this->db->select('offer_id, pid, number_of_days, isHoliday')
                      ->where('isDetailUpdated', false)
                      ->limit($limit)
                      ->get($this->offersTableToUpdate);
        $result = array();
        if($query->num_rows() > 0){
            foreach ($query->result_array() as $row)
            {   
                $result[] = $row;
            }
        }
        return $result;
    }

    private function detailUpdateSingleOffer($offerDetailsEnpointName, $offerBasicInfo){
        $fetchAdditionalDetails = true;
       
        $detailsArray = $this->getAdditionalDetailsArray($fetchAdditionalDetails, $offerBasicInfo['pid'], $offerDetailsEnpointName);
        $updateArray = array();
        $this->processAdditionalOfferData($detailsArray, $updateArray);
        
        if($updateArray != null && !empty($updateArray)){
            parent::update($this->offersTableToUpdate, $offerBasicInfo['offer_id'], $updateArray, "offer_id");
        }
    }

    private function markAdditionalTablesUpdatedForOffer($offer_id){
        parent::update($this->offersTableToUpdate, $offer_id, array('areAdditionalTablesUpdated'=> true), 'offer_id');
    }

    public function updateAdditionalTables(){
        $query = $this->db->select('offer_id, pid, number_of_days, isHoliday, dates_json, hotelsdata_json')
                          ->where('areAdditionalTablesUpdated', false)
                          ->limit(2*$this->MAX_OFFERS_FOR_SINGLE_BATCH)
                          ->get($this->offersTableToUpdate);
        $result = false;
        if($query->num_rows()>0){
            foreach ($query->result_array() as $row)
            {   
                if(array_key_exists("dates_json", $row) ){
                    $this->updateDates($row['pid'], $row["dates_json"], $row['number_of_days'], $this->fetchAdditionalDetails, $row['isHoliday'], $this->datesForOffersTableToUpdate);
                }
                if($row['isHoliday'] && array_key_exists("hotelsdata_json", $row)){
                    $this->updateHotelsForOffers($row["hotelsdata_json"], $row['pid'], $this->hotelsForOffersTableToUpdate);
                }
                $this->markAdditionalTablesUpdatedForOffer($row['offer_id']);
            }
            $result = true;
        }
        return $result;
    }

    private function getSupportedTuroperators(){
        $query = $this->db->select('TUROPERATOR_ID')
                 ->where('shouldBeIncluded', true)
                 ->get($this->turoperatorsTable);
        $result = array();
        if($query->num_rows()>0){
            $result = $query->result_array();
        }
        return $result;
    }
    private function shouldInsertTuroperator($turoperatorId){
        $supportedOperatorsIds = $this->getSupportedTuroperators();
        return in_array($turoperatorId, $supportedOperatorsIds[0]);
    }

    private function executeBasicOfferInfoInsertOperation($turoperatorsArray)
    {
        foreach($turoperatorsArray as $singleTuroperatorInfo)
        {
            if($this->shouldInsertTuroperator($singleTuroperatorInfo['TUROPERATOR_ID'])){
                $this->insertExcursions($singleTuroperatorInfo['TUROPERATOR_ID']);
                $this->insertHolidays($singleTuroperatorInfo['TUROPERATOR_ID']);
            }
        } 
    }

    private function executeDetailedOfferUpdate($offersForUpdate)
    {
        foreach($offersForUpdate as $singleOffer)
        {
            $offerDetailsEnpointName = $this->excursionsDetailsEndpointName;
            if($singleOffer['isHoliday']){
                $offerDetailsEnpointName = $this->holidaysDetailsEndpointName;
            }   
            $this->detailUpdateSingleOffer($offerDetailsEnpointName, $singleOffer);
        }
    }

    public function updateAction($fetchAdditionalDetails)
    {
        $this->fetchAdditionalDetails = $fetchAdditionalDetails;
        if(!$fetchAdditionalDetails){
            $this->truncateDynamicTables();
            $turoperatorsArray = $this->getTuroperatorsArray();
            $this->executeBasicOfferInfoInsertOperation($turoperatorsArray);
        }else if($this->shouldRunDetailedUpdate()){
            $offersForUpdate = $this->getOffersForDetailedUpdate();
            if(empty($offersForUpdate)){
                $allAdditionalTalesUpdated = $this->updateAdditionalTables();
                if(!$allAdditionalTalesUpdated){
                    $this->switchReadAndUpdateTables();
                    $this->updateLastTablesUpdateDateSetting(date($this->datesFormat, now('Europe/Sofia')));
                } 
            }else{
                $this->executeDetailedOfferUpdate($offersForUpdate);
            }
        }
    }
}
