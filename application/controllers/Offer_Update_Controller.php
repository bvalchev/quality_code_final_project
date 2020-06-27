<?php

/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 15.2.2020 г.
 * Time: 16:31
 */

class Offer_Update_Controller extends Basic_Controller
{
    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 6000);
        $this->load->model('Offer_Update_Model');
    }

    public function updateTuroperators(){
        $this->Offer_Update_Model->fillTuroperatorsTable();
    }
    public function basicRunUpdate(){
        $this->Offer_Update_Model->updateAction(false);
    }
    public function detailRunUpdate(){
        $this->Offer_Update_Model->updateAction(true);
    }

    public function updateDatesAndHotels(){
        set_time_limit(0);
        $this->Offer_Update_Model->updateAdditionalTables();
    }

    public function updateTuroperatorsTable(){
        $this->Offer_Update_Model->updateTuroperatorsTable();
    }
}