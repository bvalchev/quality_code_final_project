<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Author: Boyan Valchev
 * Date: 14.6.2020 г.
 * Time: 13:43
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class QueryParameters {
    public function __construct($rawData, $keysForLikeOperator){
        $offset = DEFAULT_GET_REQUEST_OFFSET;
        $limit = DEFAULT_GET_REQUEST_LIMIT;
        $sortOrder = DEFAULT_GET_REQUEST_SORT_ORDER;
        $sortField = null;
        $this->likeOperatorValues = array();
        $this->requestParameters = array();
        $this->rawData = $rawData;
        $this->keysForLikeOperator = $keysForLikeOperator;
        $this->build();

    }

    public function setRawData($rawData){
        $this->rawData = $rawData;
    }

    public function setKeysForLikeOperator($keysForLikeOperator){
        $this->keysForLikeOperator = $keysForLikeOperator;
    }

    public function getOffset(){
        return $this->offset;
    }

    public function getLimit(){
        return $this->limit;
    }

    public function getSortOrder(){
        return $this->sortOrder;
    }

    public function getSortField(){
        return $this->sortField;
    }

    public function getNonSpecialRequestParametes(){
        return $this->requestParameters;
    }

    public function getLikeOperatorValues(){
        return $this->likeOperatorValues;
    }


    private $keysForLikeOperator;
    private $likeOperatorValues;
    private $requestParameters;
    private $offset;
    private $limit;
    private $sortOrder;
    private $sortField;
    private $rawData;

    private function build(){
        foreach ( $this->rawData as $key => $value ) {
            if ($key == 'offset') {
                $this->offset = $value;
            } else if ($key == 'limit') {
                $this->limit = $value;
            }else if($key == 'sortField'){
                $this->sortField = $value; 
            }else if($key == 'sortOrder'){
                $this->sortOrder = $value;
            }else if(in_array($key, $this->keysForLikeOperator)) {
                $this->likeOperatorValues[$key] = $value;
            }else if($this->isParameterBooleanValue($value)){
                $this->handleBooleanParameterValue($this->requestParameters, $value);
            }else{
                $this->requestParameters[$key] = $value;
            }
        }
    }

    private function isParameterBooleanValue($parameterValue){
        return strtoupper($parameterValue) == 'TRUE' ||  strtoupper($parameterValue) == 'FALSE';
    }

    private function handleBooleanParameterValue(&$resultArray, $parameterValue){
        $valueToAssign = strtoupper($parameterValue) == 'TRUE';    
        $resultArray[$key] = $valueToAssign;
    }
}