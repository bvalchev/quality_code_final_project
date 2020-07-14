<?php

require_once APPPATH.'controllers/Offers_Controller.php';


class Offers_Unit_Test extends Offers_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('unit_test');
    }

    public function testOffers(){
        $this->testGetAll();
        $this->testGetAllWithPostMethod();
        $this->testGetSingleOffer();
        $this->testGetSingleOfferWithoutPid();
        $this->testCountryFiltering();
        $this->testMaxPriceFiltering();
        $this->testMaxAndMinPriceFiltering();
        $this->testDistinctCountries();
    }

    private function testGetAll(){
        $testName = 'Try to get all offers. Check if count matches';
        $postParams = [];
        $method = 'get';
        $test = count(json_decode($this->getOffers($postParams, $method)));
        $expectedResult = 126;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    public function testGetAllWithPostMethod(){
        $testName = 'Try to get all offers with post method. Error message should appear';
        $postParams = json_encode([]);
        $method = 'post';
        $test = $this->getOffers($postParams, $method);
        $expectedResult = false;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testDateFiltering(){
        //FAILS....FIX IT
        $testName = 'Try to get all offers. Check if count matches';
        $postParams = [];
        $method = 'get';
        $test = count($this->getOffers($postParams, $method));
        $expectedResult = 126;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testCountryFiltering(){
        $testName = 'Try to get offers for Greece. Check if count matches';
        $postParams = ['countries' => 'Гърция'];
        $method = 'get';
        $test = count(json_decode($this->getOffers($postParams, $method)));
        $expectedResult = 11;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testMaxPriceFiltering(){
        $testName = 'Test max price filtering. Check if count matches';
        $postParams = ['maxPrice' => 100];
        $method = 'get';
        $test = count(json_decode($this->getOffers($postParams, $method)));
        $expectedResult = 8;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testMaxAndMinPriceFiltering(){
        $testName = 'Test max and min price filtering. Check if count matches';
        $postParams = ['maxPrice' => 200, 'minPrice' => 100];
        $method = 'get';
        $test = count(json_decode($this->getOffers($postParams, $method)));
        $expectedResult = 12;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testGetSingleOffer(){
        $testName = 'Try to get single offer. Check if object matches';
        $postParams = ['pid'=> 11000549];
        $method = 'get';
        $test = count(json_decode($this->getOffers($postParams, $method)));
        var_dump($test);
        $expectedResult = 1;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testGetSingleOfferWithoutPid(){
        $testName = 'Try to get single offer, without providing pid. Error message should appear.';
        $postParams = [];
        $method = 'get';
        $test = $this->getDetailsForOffer($postParams, $method);
        $expectedResult = false;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testDistinctCountries(){
        $testName = 'Try to get distinct offer countries. Check if count matches';
        $method = 'get';
        $test = count(json_decode($this->getDistinctOfferCountries($method)));
        $expectedResult = 25;
        echo $this->unit->run($test, $expectedResult, $testName);
    }

    private function testGetHotelInfo(){

    }
}