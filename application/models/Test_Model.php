<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 3.3.2020 г.
 * Time: 14:40
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Test_Model extends CI_Model{
    /**
     * Overrides the method of the ancestor
     * Inserts a tuple in the clients table
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getJson(){
        $query = file_get_contents('http://json.peakview.bg/b2b_programi_list_ekskurzii.php?us=APIUSER&ps=APIKEY&Turoperator_ID=11');
        return $query;
    }
}