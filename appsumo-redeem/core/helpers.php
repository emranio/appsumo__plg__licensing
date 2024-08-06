<?php 
namespace Appsumo_Redeem\Core;
defined( 'ABSPATH' ) || exit;

class Helpers{
    static function array_key_last(array $array) {
        if( !empty($array) ) return key(array_slice($array, -1, 1, true));
    }
    
    static function get_campaign_data(){
        $result = [];
        $result['download_id'] = ((isset($_POST['download_id']) && is_numeric($_POST['download_id'])) ? $_POST['download_id'] : 0);
        if(\Appsumo_Redeem::campaign_data($result['download_id']) == null){
            die('Error code 1001; Oma wa hokage dattebayoo! Please Contact support.');
        }
        $result['campaign_data'] = \Appsumo_Redeem::campaign_data($result['download_id']);
        $result['price_id'] = $result['campaign_data']->price_id;

        return (object) $result;
    }
}