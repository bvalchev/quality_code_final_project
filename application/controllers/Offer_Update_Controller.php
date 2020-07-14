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
        $this->load->model('Offer_Update_Model');
    }

    public function updateTuroperators(){
        $this->Offer_Update_Model->updateTuroperatorsTable();
    }
    public function basicRunUpdate(){
        $this->Offer_Update_Model->updateAction(false);
    }
    public function detailRunUpdate(){
        $this->Offer_Update_Model->updateAction(true);
    }

    public function updateDatesAndHotels(){
        $this->Offer_Update_Model->updateAdditionalTables();
    }

    public function updateTuroperatorsTable(){
        $this->Offer_Update_Model->updateTuroperatorsTable();
    }
}