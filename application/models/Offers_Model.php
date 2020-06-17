
<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 3.3.2020 г.
 * Time: 14:40
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Offers_Model extends Basic_Model{
    /**
     * Overrides the method of the ancestor
     * Inserts a tuple in the clients table
     */
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Offer_Update_Model');
       // ini_set("memory_limit","128M");
    }
    private $europeCountries = array('Русия', 'Украйна', 'Франция', 'Испания', 'Швеция', 'Норвегия', 'Германия', 'Финландия', 
                                    'Полша', 'Италия', 'Англия', 'Великобритания', 'Румъния', 'Беларус', 'Гърция', 'България', 
                                    'Исландия', 'Унгария', 'Португалия', 'Сърбия', 'Азербайджан', 'Австрия', 'Чехия', 'Ирландия', 
                                    'Литва', 'Латвия', 'Хърватия', 'Босна', 'Херцеговина', 'Словакия', 'Естония', 'Дания', 'Холандия',
                                    'Нидерландия', 'Швейцария', 'Молдова', 'Белгия', 'Албания', 'Северна Македония', 'Македония',
                                    'Турция', 'Словения', 'Черна гора', 'Кипър', 'Люксембург', 'Андора', 'Малта', 'Лихтенщайн', 
                                    'Сан Марино', 'Монако', 'Ватикан');
    private $specialDates = array('23-12-2020', '24-12-2020', '25-12-2020', '26-12-2020',  '31-12-2020', '01-01-2021', '18-04-2020');

    private function getFiltratedDatesArray($filterDataAsArray, $limit, $offset){
        $this->db->select('DISTINCT `offer_pid`', FALSE);
        $this->db->from($this->datesForOffersTableToRead);
        if(array_key_exists('startDate', $filterDataAsArray)){
            $this->db->where('date_start =', date('Y-m-d', strtotime($filterDataAsArray['startDate'])));
        }
        if(array_key_exists('endDate', $filterDataAsArray)){
            $this->db->where('date_end =', date('Y-m-d', strtotime($filterDataAsArray['endDate'])));
        }
        if(array_key_exists('afterDate', $filterDataAsArray)){
            $this->db->where('date_start >', date('Y-m-d', strtotime($filterDataAsArray['afterDate'])));
        }
        if(array_key_exists('beforeDate', $filterDataAsArray)){
            $this->db->where('date_end <', date('Y-m-d', strtotime($filterDataAsArray['beforeDate'])));
        }
        if(array_key_exists('isEarly', $filterDataAsArray)){
            $this->db->where('date_start >', date('Y-m-d', strtotime("+5 months")));
        }
      
        if(array_key_exists('isHoliday', $filterDataAsArray)){
            foreach($this->specialDates as $singleSpecialDate){
                $this->db->or_where('date_start = ',  date('Y-m-d', strtotime($singleSpecialDate)));
            }   
        }
        $this->db->limit(10000);
        //$this->db->order_by('RAND()');

        $query = $this->db->get();
        $result = array();
        if($query->num_rows()> 0){
            foreach($query->result_array() as $row){
                $result[] = $row['offer_pid'];
            }
        }
        return $result;
    }

    private function isDateFiltrationUsed($filterDataAsArray){
        return (array_key_exists('startDate', $filterDataAsArray) ||
                array_key_exists('endDate', $filterDataAsArray) ||
                array_key_exists('afterDate', $filterDataAsArray) ||
                array_key_exists('beforeDate', $filterDataAsArray) ||
                array_key_exists('isHoliday', $filterDataAsArray) ||
                array_key_exists('isEarly', $filterDataAsArray)
        );
    }

    public function getOperation($filterDataAsArray, $arrayForLikeOperator, $limit, $offset, $sortField = null, $sortOrder = null){
         $filtratedOfferPids = $this->getFiltratedDatesArray($filterDataAsArray, $limit, $offset);
        // var_dump($filtratedOfferPids);
        // die();
        
        $fieldsToSelect = (array_key_exists('getFullInfo', $filterDataAsArray) && $filterDataAsArray['getFullInfo'] = 'TRUE') ?
                          '*' : 
                          "offer_id, pid, turoperator_name, isHoliday, country, 
                           (case WHEN LENGTH(title) > 40 THEN CONCAT(substr(title, 1, 40), '...') ELSE title END) as title,
                           dates, min_price, number_of_days, main_image, main_image_big, transport_type" ;
        $shouldAddAnd = false;
        $this->db->select($fieldsToSelect);
        $this->db->from($this->offersTableToRead);
        //$this->db->order_by('rand()');
        if(array_key_exists('countries', $filterDataAsArray)){
            $countries = array($filterDataAsArray['countries']);
            if(strpos($filterDataAsArray['countries'], ',') ){
                $countries = explode ( ',', $filterDataAsArray['countries']); 
            }
            foreach($countries as $singleCountry){
                $this->db->or_like($this->offersTableToRead.'.country', $singleCountry);
                // $this->db->or_like($this->offersTableToRead.'.description', $singleCountry);
                // $this->db->or_like($this->offersTableToRead.'.description_clean', $singleCountry);
            }
        }
        if(array_key_exists('cities', $filterDataAsArray)){
            $cities = array($filterDataAsArray['cities']);
            if(strpos($filterDataAsArray['cities'], ',') ){
                $cities = explode ( ',', $filterDataAsArray['cities']); 
            }
            foreach($cities as $singleCity){
                $this->db->or_like($this->offersTableToRead.'.title', $singleCountry);
                $this->db->or_like($this->offersTableToRead.'.description', $singleCity);
                $this->db->or_like($this->offersTableToRead.'.description_clean', $singleCity);
            }
        }
        if(array_key_exists('maxPrice', $filterDataAsArray)){
            $this->db->where($this->offersTableToRead.'.min_price <=', $filterDataAsArray['maxPrice']);
        }
        if(array_key_exists('minPrice', $filterDataAsArray)){
            $this->db->where($this->offersTableToRead.'.min_price >=', $filterDataAsArray['minPrice']);
        }
        if(array_key_exists('transport', $filterDataAsArray)){
            $this->db->like('transport_type', $filterDataAsArray['transport']);
        }
        
        //$this->db->join($this->datesForOffersTableToRead, $this->datesForOffersTableToRead.'.offer_pid = '.$this->offersTableToRead.'.pid');
        //$this->db->join($this->hotelsForOffersTableToRead, $this->hotelsForOffersTableToRead.'.offer_pid = '.$this->offersTableToRead.'.pid');
        if($arrayForLikeOperator != []){
            $this->db->group_start();
            foreach ($arrayForLikeOperator as $key => $value) {
                $this->db->or_like($key, $value);
            }
            $this->db->group_end();
        }
        // if(array_key_exists('startDate', $filterDataAsArray)){
        //     $this->db->where($this->datesForOffersTableToRead.'.date_start =', date('Y-m-d', strtotime($filterDataAsArray['startDate'])));
        // }
        // if(array_key_exists('endDate', $filterDataAsArray)){
        //     $this->db->where($this->datesForOffersTableToRead.'.date_end =', date('Y-m-d', strtotime($filterDataAsArray['endDate'])));
        // }
        // if(array_key_exists('afterDate', $filterDataAsArray)){
        //     $this->db->where($this->datesForOffersTableToRead.'.date_start >', date('Y-m-d', strtotime($filterDataAsArray['afterDate'])));
        // }
        // if(array_key_exists('beforeDate', $filterDataAsArray)){
        //     $this->db->where($this->datesForOffersTableToRead.'.date_end <', date('Y-m-d', strtotime($filterDataAsArray['beforeDate'])));
        // }
      
        // if(array_key_exists('isHoliday', $filterDataAsArray)){
        //     foreach($this->specialDates as $singleSpecialDate){
        //         $this->db->or_where($this->datesForOffersTableToRead.'.date_start = ',  date('Y-m-d', strtotime($singleSpecialDate)));
        //     }   
        // }
        
        if($this->isDateFiltrationUsed($filterDataAsArray)){
            if(empty($filtratedOfferPids)){
                $this->db->where('pid', -1);
            }else{
                $this->db->where_in('pid', $filtratedOfferPids);
            }
        }

        
    
        if(array_key_exists('isExotic', $filterDataAsArray)){
            if($filterDataAsArray['isExotic'] == 'true'){
                $this->db->where_not_in($this->offersTableToRead.'.country', $this->europeCountries);
            }else if($filterDataAsArray['isExotic'] == 'false'){
                $this->db->where_in($this->offersTableToRead.'.country', $this->europeCountries);
            }
        }
        if($sortField != null){
            if($sortOrder != null && in_array($sortOrder, array('ASC', 'DESC', 'RANDOM'))) {
                $this->db->order_by($sortField, $sortOrder);
            }else{
                $this->db->order_by($sortField, 'asc');
            }
        }else if(array_key_exists('getNewest', $filterDataAsArray)){
            $this->db->where('last_updated IS NOT NULL');
            $this->db->order_by('Cast(SUBSTR(last_updated, 1, LENGTH(last_updated) - 2) as date) DESC');
        }else{
            $this->db->order_by('RAND()');
        }

        $this->db->limit($limit, $offset);
       $query=$this->db->get();
        
        return $this->convertArrayToJson($query);
    }

    public function getDetailsForOffer($pid, $isHoliday){
        $query = $this->db->select('*')
                          ->from($this->offersTableToRead)
                          ->where($this->offersTableToRead.'.pid', $pid)
                          ->where($this->offersTableToRead.'.isHoliday', $isHoliday)
                          ->get();
        return $this->convertArrayToJson($query);
    }

    public function getDistinctOfferCountries(){
        $query = $this->db->select('DISTINCT `country`', FALSE)
                ->order_by('country')
                ->from($this->offersTableToRead)
                ->get();

        return $this->convertArrayToJson($query);
    }

    public function getHotelInfo($pid, $hotelId){
        $whereConditions = array();
        $whereConditions['hotel_id'] = $hotelId;
        $whereConditions['offer_pid'] = $pid;
        $query = $this->db->select('*')
                 ->where($whereConditions)
                 ->from($this->hotelsForOffersTableToRead)
                 ->get();
        return $this->convertArrayToJson($query);
    }
}
