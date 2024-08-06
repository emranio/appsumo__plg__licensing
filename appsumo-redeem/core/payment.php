<?php 
namespace Appsumo_Redeem\Core;
defined( 'ABSPATH' ) || exit;

class Payment extends \EDD_Payment{

    
    public function save_and_get_ID() {
        if(parent::save() == true){
            return $this->ID;
        }
        return false;
    }

    public function save() {
        parent::save();
        return $this;
    }

    public function get_ID(){
        return $this->ID;
    }
}