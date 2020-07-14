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

    public function __construct()
    {
        parent::__construct();
        $this->getThirdPartyAccessInfo();
    }

    public function updateAction($fetchAdditionalDetails)
    {
        $this->fetchAdditionalDetails = $fetchAdditionalDetails;
        if($fetchAdditionalDetails && $this->shouldRunDetailedUpdate()){
            $offersForUpdate = $this->getOffersForDetailedUpdate();
            $areAllOffersUpdated = empty($offersForUpdate);
            if($areAllOffersUpdated){
                $isAnyAdditionalTableUpdated = $this->updateAdditionalTables();
                if(!$isAnyAdditionalTableUpdated){
                    $this->switchReadAndUpdateTables();
                    $this->updateLastTablesUpdateDateSetting(date(DATE_FORMAT, now('Europe/Sofia')));
                } 
            }else{
                $this->updateOfferWithDetails($offersForUpdate);
            }
        }else{ 
            $this->truncateDynamicTables();
            $turoperatorsArray = $this->getTuroperatorsArray();
            $this->insertBasicOfferInfo($turoperatorsArray);
        }
    }

    public function updateTuroperatorsTable(){
        $turoperatorsArray = $this->getTuroperatorsArray();
        foreach($turoperatorsArray as $singleTuroperatorInfo){
            $insertData = array();
            $insertData['TUROPERATOR_ID']= $singleTuroperatorInfo['TUROPERATOR_ID'];
            $insertData['name']= $singleTuroperatorInfo['TUROPERATOR'];
            $this->insert(TUROPERATORS_TABLE_NAME, $insertData);
        }
    }
    
    public function getTuroperatorsArray()
    {
        $getTuroperatorsUrl = $this->thirdPartyBaseUrl . "b2b_turoperator_spisak.php?" . $this->thirdPartyCredentials;
        $turoperatorsJson = file_get_contents($getTuroperatorsUrl);
        $turoperatorsArray = json_decode($turoperatorsJson, true);
        return $turoperatorsArray;
    }
     
    public function processAdditionalOfferData($rawData, &$remappedData){
        $remappedData['isDetailUpdated'] = true;
        if($rawData != null){
            $remappedData = array_merge($remappedData, $this->remapOrdinaryKeys($rawData, THIRD_PARTY_ADDITIONAL_KEYS_MAP));
            $remappedData = array_merge($remappedData, $this->remapImageKeys($rawData, THIRD_PARTY_ADDITIONAL_IMAGE_KEYS_MAP));
            $remappedData = array_merge($remappedData, $this->remapJsonKeys($rawData, THIRD_PARTY_ADDITIONAL_JSON_KEYS_MAP));
        }
    }

    public function updateHotelsForOffers($hotelsForOffersJson, $pid, $table){
        $hotelsForOffersArray = json_decode($hotelsForOffersJson, true);
        if($hotelsForOffersArray != null)
        {
            foreach($hotelsForOffersArray as $singleTuple)
            {
                if(is_array($singleTuple)){
                    $hotelsDataToInsert =  $this->remapOrdinaryKeys($singleTuple, THIRD_PARTY_HOTEL_KEYS_MAP);
                    $hotelsDataToInsert['offer_pid '] = $pid;
                    $this->insert($this->updateHotelsForOffersTable, $hotelsDataToInsert);
                }       
            }  
        }
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
            $offerDetailsJson = file_get_contents($offerDetailsInfoUrl);
            $offerDetailsArray = json_decode($offerDetailsJson, true);
            
            if(!empty($offerDetailsArray)){
                $offerDetailsArray = $offerDetailsArray[0];
            }
        }
        return $offerDetailsArray;
    }

    public function updateAdditionalTables(){
        $query = $this->db->select('offer_id, pid, number_of_days, isHoliday, dates_json, hotelsdata_json')
                          ->where('areAdditionalTablesUpdated', false)
                          ->limit($this->MAX_OFFERS_FOR_SINGLE_BATCH)
                          ->get($this->updateOffersTable);
        $result = false;
        if($query->num_rows()>0){
            foreach ($query->result_array() as $row)
            {   
                if(array_key_exists("dates_json", $row) ){
                    $this->updateDates($row['pid'], $row["dates_json"], $row['number_of_days'], $this->fetchAdditionalDetails, $row['isHoliday'], $this->updateDatesForOffersTable);
                }
                if($row['isHoliday'] && array_key_exists("hotelsdata_json", $row)){
                    $this->updateHotelsForOffers($row["hotelsdata_json"], $row['pid'], $this->updateHotelsForOffersTable);
                }
                $this->markAdditionalTablesUpdatedForOffer($row['offer_id']);
            }
            $result = true;
        }
        return $result;
    }

    public function updateDates($pid, $dates, $daysToAdd, $isDatesJsonAvailable, $isHoliday, $table){
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
            $this->insert($table, $dataToInsert);
        }
    }

    private $fetchAdditionalDetails = true;
    private $MAX_OFFERS_FOR_SINGLE_BATCH = MAX_OFFERS_FOR_SINGLE_BATCH;
    private $thirdPartyBaseUrl;
    private $thirdPartyCredentials;

    private function switchReadAndUpdateTables()
    {
        $this->db->update(SETTINGS_TABLE_NAME, array('setting_value' => $this->updateOffersTable), array('setting_name' => 'offersTableToRead'));
        $this->db->update(SETTINGS_TABLE_NAME, array('setting_value' => $this->readOffersTable), array('setting_name' => 'offersTableToUpdate'));
        $this->db->update(SETTINGS_TABLE_NAME, array('setting_value' => $this->updateDatesForOffersTable), array('setting_name' => 'datesForOffersTableToRead'));
        $this->db->update(SETTINGS_TABLE_NAME, array('setting_value' => $this->readDatesForOffersTable), array('setting_name' => 'datesForOffersTableToUpdate'));
        $this->db->update(SETTINGS_TABLE_NAME, array('setting_value' => $this->updateHotelsForOffersTable), array('setting_name' => 'hotelsForOffersTableToRead'));
        $this->db->update(SETTINGS_TABLE_NAME, array('setting_value' => $this->readHotelsForOffersTable), array('setting_name' => 'hotelsForOffersTableToUpdate'));
    }

    private function insertIntoOffersTable($offerBaseInfoUrl, $offerDetailsEnpointName, $isHoliday = false)
    {
        $offersForTuroperatorJson = file_get_contents($offerBaseInfoUrl);
        $offersForTuropatorArray = json_decode($offersForTuroperatorJson, true);
        $fetchAdditionalDetails = $this->fetchAdditionalDetails;
        
        if($offersForTuropatorArray == null){
            return;
        }
        foreach($offersForTuropatorArray as $singleOffer)
        {   
            $offerDetailsArray = $this->getAdditionalDetailsArray($fetchAdditionalDetails, $singleOffer["PID"], $offerDetailsEnpointName);
            $dataToInsert = $this->getOfferInsertData($singleOffer, $offerDetailsArray, $fetchAdditionalDetails, $isHoliday);
            $this->insert($this->updateOffersTable, $dataToInsert);
        }
    }

    private function getDatesDataToInsert($pid, $singleTuple, $daysToAdd, $isHoliday, $fetchAdditionalDetails)
    {
        $dataToInsert = array();
        $dataToInsert['offer_pid'] = $pid;
        $dataToInsert['isHoliday'] = $isHoliday;
        if($fetchAdditionalDetails){
            $dataToInsert['date_start'] = date(DATE_FORMAT, strtotime($singleTuple['data']));
            $dataToInsert['date_min_price'] = $singleTuple['data_price'];
            $dataToInsert['date_currency'] = $singleTuple['data_valuta'];
	        $dataToInsert['date_end'] = date(DATE_FORMAT,strtotime($singleTuple['data'] . ' + '.$daysToAdd.' days'));
        }else{
            $dataToInsert['date_start'] = date(DATE_FORMAT, strtotime($singleTuple));
            $dataToInsert['date_end'] = date(DATE_FORMAT, strtotime($singleTuple . ' + '.$daysToAdd.' days'));
        }
        return $dataToInsert;
    }

    private function insertExcursions($turoperatorId/*, &$shouldStopCounter, &$offersInsertedCounter*/)
    {
        $getExcursionsForTuroperatorUrl = $this->thirdPartyBaseUrl . 
                                        EXCURSIONS_LIST . "?" .
                                        $this->thirdPartyCredentials .
                                        "&Turoperator_ID=" . 
                                        $turoperatorId; 
        $isHoliday = false;
        $this->insertIntoOffersTable($getExcursionsForTuroperatorUrl, EXCURSIONS_DETAIL_ENDOPOINT/*, $shouldStopCounter*/, $isHoliday);
    }

    private function insertHolidays($turoperatorId/*, &$shouldStopCounter, &$offersInsertedCounter*/)
    {
        $getHolidaysForTuroperatorUrl = $this->thirdPartyBaseUrl . 
                                        HOLIDAYS_LIST . "?".
                                        $this->thirdPartyCredentials .
                                        "&Turoperator_ID=" . 
                                        $turoperatorId;
        $isHoliday = true;
        $this->insertIntoOffersTable($getHolidaysForTuroperatorUrl, HOLIDAYS_DETAIL_ENDPOINT/*, $shouldStopCounter*/, $isHoliday);
    }

    private function shouldRunDetailedUpdate()
    {
        $lastTablesUpdateDateString = $this->getSettingValue('lastTablesUpdateDate');
        return (date(DATE_FORMAT, strtotime($lastTablesUpdateDateString)) < date(DATE_FORMAT, now('Europe/Sofia')));
    }

    private function updateLastTablesUpdateDateSetting($valueToSet){
        $data = array(
            'setting_id' => 7,
            'setting_name' => 'lastTablesUpdateDate',
            'setting_value' => $valueToSet
        );
        $this->db->where('setting_name', 'lastTablesUpdateDate');
        $this->db->update(SETTINGS_TABLE_NAME, $data);
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
                      ->get($this->updateOffersTable);
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
            $this->update($this->updateOffersTable, $offerBasicInfo['offer_id'], $updateArray, "offer_id");
        }
    }

    private function markAdditionalTablesUpdatedForOffer($offer_id){
        $this->update($this->updateOffersTable, $offer_id, array('areAdditionalTablesUpdated'=> true), 'offer_id');
    }

    private function getThirdPartyAccessInfo(){
        if(IS_PRODUCTION){
            $this->thirdPartyBaseUrl = THIRD_PARTY_URL;
            $this->thirdPartyCredentials = AUTHENTICATION_KEY;
        
        }else{
            $this->thirdPartyBaseUrl = DEMO_THIRD_PARTY_URL;
            $this->thirdPartyCredentials = DEMO_AUTHENTICATION_KEY;
        }
    }

    private function truncateDynamicTables()
    {
        $this->db->empty_table($this->updateOffersTable);
        $this->db->empty_table($this->updateDatesForOffersTable);
        $this->db->empty_table($this->updateHotelsForOffersTable);
    }

    private function getSupportedTuroperators(){
        $query = $this->db->select('TUROPERATOR_ID')
                 ->where('shouldBeIncluded', true)
                 ->get(TUROPERATORS_TABLE_NAME);
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

    private function insertBasicOfferInfo($turoperatorsArray)
    {
        foreach($turoperatorsArray as $singleTuroperatorInfo)
        {
            if($this->shouldInsertTuroperator($singleTuroperatorInfo['TUROPERATOR_ID'])){
                $this->insertExcursions($singleTuroperatorInfo['TUROPERATOR_ID']);
                $this->insertHolidays($singleTuroperatorInfo['TUROPERATOR_ID']);
            }
        } 
    }

    private function updateOfferWithDetails($offersForUpdate)
    {
        foreach($offersForUpdate as $singleOffer)
        {
            $offerDetailsEnpointName = ($singleOffer['isHoliday'] ?  HOLIDAYS_DETAIL_ENDPOINT :  EXCURSIONS_DETAIL_ENDOPOINT);  
            $this->detailUpdateSingleOffer($offerDetailsEnpointName, $singleOffer);
        }
    }

    private function getOfferInsertData($rawData, $detailsArray, $fetchAdditionalDetails, $isHoliday)
    {
        $remappedData = $this->remapOrdinaryKeys($rawData, THIRD_PARTY_BASIC_KEYS_MAP);
        $remappedData = array_merge($remappedData, $this->remapImageKeys($rawData, THIRD_PARTY_BASIC_IMG_KEYS_REMAP));
        $remappedData['isHoliday'] = $isHoliday;
        $remappedData['isDetailUpdated'] = false;
        if($fetchAdditionalDetails){
            $this->processAdditionalOfferData($detailsArray, $dataToInsert);
        }
        return $remappedData;
    }

    private function remapOrdinaryKeys($rawData, $map){
        $remappedData = array();
        foreach ($map as $key => $value){
            if(array_key_exists($key, $rawData)){
                $remappedData[$value] = $rawData[$key];
            }
        };
        return $remappedData;
    }

    private function remapImageKeys($rawData, $map){
        $remappedData = array();
        foreach ($map as $key => $value){
            if(array_key_exists($key, $rawData)){
                $remappedData[$value] = preg_replace("/^http:/i", "https:", $rawData[$key]);
            }
        };
        return $remappedData;
    }

    private function remapJsonKeys($rawData, $map){
        $remappedData = array();
        foreach ($map as $key => $value){
            if(array_key_exists($key, $rawData)){
                $remappedData[$value] = json_encode($rawData[$key]);
            }
        };
        return $remappedData;
    }
}
