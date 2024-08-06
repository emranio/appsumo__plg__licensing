<?php 
namespace Appsumo_Redeem\Core;
defined( 'ABSPATH' ) || exit;

use Appsumo_Redeem\Core\Validate;
use Appsumo_Redeem\Core\Input;

class Register{
    use \Appsumo_Redeem\Traits\Singleton;
    use \Appsumo_Redeem\Traits\Input;

    public $result;


    public function purchase(){
        $this->result = (object)[
            'success' => true,
            'message' => [],
        ];
        

        // validate user data
        $validation = Validate::instance()->check_create($this->get(), ['email', 'name', 'code', 'password']); // checkig for creating new entry
        if($validation->success !== true){
            return $validation;
        }

        $has_user = get_user_by( 'email', $this->get('email'));

        if($has_user != false){
            $user_id = $has_user->ID;
        }else{
            // create WP user
            $userdata = array(
                'user_login' =>  $this->get('email'),
                'user_email' =>  $this->get('email'),
                'nickname'   =>  $this->get('name'),
                'display_name'   =>  $this->get('name'),
                'show_admin_bar_front'   => 'false',
                'user_pass'  =>  $this->get('password') // When creating an user, `user_pass` is expected.
            );
            
            $user_id = wp_insert_user( $userdata );
            
            if ( is_wp_error( $user_id ) ) {
                $this->result->success = false;
                $this->result->message[] = esc_html__( 'Failed creating new user. Please contact customer support.', 'edd-pomo' );
                
                return $this->result;
            }
        }
        
        $customer_id = Validate::instance()->find_customer($this->get('email'));

        if($customer_id == false){
            // create EDD customer
            $customer = new \EDD_Customer;
            $customer_args = [
                'user_id' => $user_id,
                'email' => $this->get('email'),
                'name' => $this->get('name'),
                'notes' => 'Automatically created during appsumo redeem',
            ];
            
            $customer_id = $customer->create( $customer_args );
        }
        
        if($customer_id == false){
            $this->result->success = false;
            $this->result->message[] = esc_html__( 'Failed creating new customer. Please contact customer support.', 'edd-pomo' );

            return $this->result;
        }
        
        



        // purchase the product
        $purchase = Purchase::instance()->set([
            'id' => $customer_id, // csutomer id
            'email' => $this->get('email'), // customer email
            'code' => $this->get('code'), // discount code
        ])->purchase();

        if($purchase == false){
            $this->result->success = false;
            $this->result->message[] = esc_html__( 'Failed to add product. Please contact customer support.', 'edd-pomo' );

            return $this->result;
        }

        // make user login
        $creds = array(
            'user_login'    => $this->get('email'),
            'user_password' => $this->get('password'),
            'remember'      => true
        );
     
        $user = wp_signon( $creds, false );
     
        if ( is_wp_error( $user ) ) {
            // echo $user->get_error_message();
        }

        $this->result->message[] = '
            Thank you for your AppSumo purchase. </br>
            If you want to stack more codes (to upgrade) <a href="https://account.wpmet.com/appsumo/'.Helpers::get_campaign_data()->download_id.'?upgrade=true" >click here</a>. </br></br>
            
            Please go to <a href="https://account.wpmet.com/">WpMet Dashboard</a> to download your product.
        ';

        return $this->result;
    }



    public function upgrade(){
        $this->result = (object)[
            'success' => true,
            'message' => [],
        ];

        // validate user data
        $validation = Validate::instance()->check_upgrade($this->get(), ['email', 'code']); // checkig for upgrading package to an existing user
        if($validation->success !== true){
            return $validation;
        }

        $customer = Validate::instance()->find_customer($this->get('email'));

        $upgrade = Purchase::instance()->set([
            'email' => $this->get('email'), // customer email
            'code' => $this->get('code'), // discount code
        ])->upgrade();

        if($upgrade->success == false){
            return $upgrade;
        }

        if($this->get('password') != ''){
            // make user login
            $creds = array(
                'user_login'    => $this->get('email'),
                'user_password' => $this->get('password'),
                'remember'      => true
            );
            
            $user = wp_signon( $creds, false );
            
            if ( is_wp_error( $user ) ) {
                // echo $user->get_error_message();
            }
        }

        $this->result->message[] = '
            AppSumo reedem code has been successfully added. </br>
            '. 

            ($upgrade->info->upgradable == 'no' ? '' : 
                'If you want to stack more codes (to upgrade) <a href="https://account.wpmet.com/appsumo/'.Helpers::get_campaign_data()->download_id.'?upgrade=true" >click here</a>. </br></br>'
            )

            .'Go to your <a href="https://account.wpmet.com/">WpMet Dashboard</a> to download your item.
        ';
        return $this->result;

    }

}