<?php 
namespace Appsumo_Redeem\Core;
defined( 'ABSPATH' ) || exit;

use Appsumo_Redeem\Core\Validate;
use Appsumo_Redeem\Core\Input;
use Appsumo_Redeem\Core\Helpers;


class Purchase{
    use \Appsumo_Redeem\Traits\Singleton;
    use \Appsumo_Redeem\Traits\Input;

    public $result;

    public $download_id;
    public $campaign_data;
    public $price_id;

    public function set_campaign_data(){
        $this->download_id = Helpers::get_campaign_data()->download_id;
        $this->campaign_data = Helpers::get_campaign_data()->campaign_data;
        $this->price_id = Helpers::get_campaign_data()->price_id;
    }

    public function purchase(){
        $this->set_campaign_data();

        $this->result = (object)[
            'success' => true,
            'message' => [],
        ];

        $payment = new \Appsumo_Redeem\Core\Payment();
        $payment->add_download( $this->download_id, [ 'quantity'    => 1, 'price_id'    => $this->price_id[1]] );
        $payment->email = $this->get('email');
        $payment->discounts = $this->get('code');
        $payment->status = 'complete';
        $payment->total = '0';
        $payment->gateway = 'appsumo';

        $payment_id = $payment->save_and_get_ID();

        $payment_note = new \Appsumo_Redeem\Core\Payment($payment_id );

        $payment_note->add_note('
            1st time purchase via AppSumo code. Redeem Code: '.$this->get('code').'
        ');
        
        $cart_item = []; $cart_item['item_number']['options']['price_id'] = $this->price_id[1];

        edd_software_licensing()->generate_license($this->download_id, $payment_id, 'default', $cart_item);

        if($payment_id != false){
            $code = Validate::instance()->find_code($this->get('code'));
            update_post_meta( $code->ID, '_edd_discount_uses', '1' );
        }


        return ($payment_id == false) ?  false : true;
    }

    public function upgrade(){
        $this->set_campaign_data();

        $this->result = (object)[
            'success' => true,
            'message' => [],
        ];
        $appsumo_item_found = false;

        $customer = Validate::instance()->find_customer($this->get('email'));

        $payments = $customer->get_payments();
        // print_r($payments);

        foreach($payments as $payment){
            if($payment->gateway == 'appsumo'){

                $payments_array = $payment->array_convert();
                
                if(isset($payments_array['payment_meta']['downloads'][0]['id']) && (int)$payments_array['payment_meta']['downloads'][0]['id'] == $this->download_id){
                    
                    $appsumo_item_found = true;
                    
                    $price_id = (int)(!isset($payments_array['payment_meta']['downloads'][0]['options']['price_id']) ? 0 : $payments_array['payment_meta']['downloads'][0]['options']['price_id'] );
                    
                    if($price_id == $this->price_id[0]){
                        $this->result->success = false;
                        $this->result->message[] = esc_html__( 'No valid purchase for "'.$this->campaign_data->name.'" is found in this account. Please contact customer support.', 'edd-pomo' );
                        return $this->result;
                    }
    
                    if($price_id == $this->price_id[Helpers::array_key_last($this->price_id)]){
                        $this->result->success = false;
                        $this->result->message[] = '
                            You\'ve already used the max number of redeem codes for this account. 
                            </br>Please use another account for more redeem codes.
                        ';
                        return $this->result;
                    }
    
                    // remove old item
                    $payment->remove_download( $this->download_id );
                    $payment->save();
    
                    edd_software_licensing()->delete_license($payment->ID, $this->download_id);
    
    
                    // add new upgraded item
                    $new_price_id_key = array_search($price_id, $this->price_id);
                    
                    $args = array(
                        'price_id' => $this->price_id[($new_price_id_key + 1)], // Variable price ID
                        'item_price' => 0.00, // Makes the item free
                    );
                    $payment->add_download( $this->download_id, $args ); // Adds Download ID 23, variable price 1 to the payment
                    $payment->save();
    
                    $payment->add_note('
                        License Price ID for '. $this->campaign_data->name .' changed from '.$this->price_id[$new_price_id_key].' to '.$this->price_id[($new_price_id_key + 1)].'. Redeem Code: '.$this->get('code').'
                    ');
    
                    $cart_item = []; $cart_item['item_number']['options']['price_id'] = $this->price_id[($new_price_id_key + 1)];
                    edd_software_licensing()->generate_license($this->download_id, $payment->ID, 'default', $cart_item);
    
                    if($payment == true){
                        $code = Validate::instance()->find_code($this->get('code'));
                        update_post_meta( $code->ID, '_edd_discount_uses', '1' );
                    }
                    
                }
            }
        }

        if($appsumo_item_found == false){
            $this->result->success = false;
            $this->result->info = [
                'new_price_id_key' => ($new_price_id_key + 1),
                'new_price_id' => $this->price_id[($new_price_id_key + 1)],
                'upgradable' => ((Helpers::array_key_last($this->price_id) == ($new_price_id_key + 1)) ? 'no' : 'yes'),
            ];
            $this->result->message[] = esc_html__( 'No AppSumo purchase is found for this account. Please contact customer support.', 'edd-pomo' );
            return $this->result;
        }

        return $this->result;

    }
}