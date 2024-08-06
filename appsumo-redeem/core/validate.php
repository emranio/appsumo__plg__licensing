<?php 
namespace Appsumo_Redeem\Core;
defined( 'ABSPATH' ) || exit;

use Appsumo_Redeem\Core\Input;

class Validate{
    use \Appsumo_Redeem\Traits\Singleton;

    public $result;
    public $parem;

    public function check_create($inputs = [], $required = []){
        $this->result = (object)[
            'success' => true,
            'message' => [],
        ];

        foreach($required as $k){
            if(!isset($inputs[$k])){
                $this->result->success = false;
                $this->result->message[] = esc_html__( ucfirst($k) . ' can not be empty.', 'edd-pomo' );
            }
        }

        foreach($inputs as $k => $v){
            switch($k){
                case 'email':
                    // check if the email is valid and not exists in user DB
                    if($v != '' && !is_email($v)){
                        $this->result->success = false;
                        $this->result->message[] = esc_html__( $v . ' is not a valid email.', 'edd-pomo' );
                    }

                    // if($this->find_customer($v) != false || get_user_by( 'email', $v ) != false){
                    //     $this->result->success = false;
                    //     $this->result->message[] = esc_html__( $v . ' is already being used. Please give a different email.', 'edd-pomo' );
                    // }

                break;
                case 'code':
                    // check if the code is valid and not used before
                    if(!$this->find_code($v)){
                        $this->result->success = false;
                        $this->result->message[] = esc_html__( $v . ' is not a valid code.', 'edd-pomo' );
                    }
                break;
            }
        }

        return $this->result;
    }

    public function check_upgrade($inputs = [], $required = []){
        $this->result = (object)[
            'success' => true,
            'message' => [],
        ];

        foreach($required as $k){
            if(!isset($inputs[$k])){
                $this->result->success = false;
                $this->result->message[] = esc_html__( ucfirst($k) . ' can not be empty.', 'edd-pomo' );
            }
        }

        foreach($inputs as $k => $v){
            switch($k){
                case 'email':
                    // check if the email is valid and not exists in user DB
                    if($v != '' && !is_email($v)){
                        $this->result->success = false;
                        $this->result->message[] = esc_html__( $v . ' is not a valid email.', 'edd-pomo' );
                    }

                    if($this->find_customer($v) == false){
                        $this->result->success = false;
                        $this->result->message[] = esc_html__( 'Couldn\'t find any user for ' . $v, 'edd-pomo' );
                    }

                break;
                case 'code':
                    // check if the code is valid and not used before
                    if(!$this->find_code($v)){
                        $this->result->success = false;
                        $this->result->message[] = esc_html__( $v . ' is not a valid code.', 'edd-pomo' );
                    }
                break;
            }
        }

        return $this->result;
    }

    public function find_customer($email = ''){
        $customer = new \EDD_Customer($email);

        if($customer->id == 0){
            return false;
        }

        return $customer;
    }

    public function find_code($code = ''){
        $args = array(
            'posts_per_page'   => 1,
            'post_type'          => 'edd_discount',
            'meta_query' => [
                [
                    'key'     => '_edd_discount_code',
                    'value'   => $code,
                    'compare' => '=',
                ],
                [
                    'key'     => '_edd_discount_uses',
                    'value'   => 1,
                    'compare' => '<',
                ],
            ],
        );
        $code_query = new \WP_Query( $args );
        $result = false;
        $redeem_code_prefix = \Appsumo_Redeem\Core\Helpers::get_campaign_data()->campaign_data->redeem_code_prefix;

        if ( $code_query->have_posts() ) {
            while ( $code_query->have_posts() ) {
                $code_query->the_post();
                if (strpos(get_the_title(), $redeem_code_prefix) !== false) {
                    return (object)[
                        'ID' => get_the_ID(),
                    ];
                }
            }
        }
        
        wp_reset_postdata();
        return $result;
    }
}
