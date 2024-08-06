<?php 
namespace Appsumo_Redeem\Api;
defined( 'ABSPATH' ) || exit;

use Appsumo_Redeem\Core\Validate;
use Appsumo_Redeem\Core\Purchase;
use Appsumo_Redeem\Core\Register;


class Redeem extends \Appsumo_Redeem\Libs\Handler_Api{

    public function config(){
        $this->prefix = 'redeem';
        // $this->param  = "/(?P<type>\w+)/";
    }

    private function inputs($name = null){
        $inputs = (isset($_POST['inputs']) ? $_POST['inputs'] : []);

        if($name == null){
            return $inputs;
        }

        return isset($inputs[$name]) ? $inputs[$name] : '';

    }

    // validate the data
    public function post_validate_create(){
        return Validate::instance()->check_create($this->inputs()); // checkig for creating new entry
    }
    public function post_validate_upgrade(){
        return Validate::instance()->check_upgrade($this->inputs());
    }

    // creates new user & attaches products to that
    public function post_create(){
        return Register::instance()->set($this->inputs())->purchase();
    }

    // creates new user & attaches products to that
    public function post_upgrade(){
        return Register::instance()->set($this->inputs())->upgrade();
    }


    public function post_test(){
        // return Validate::instance()->find_code($this->inputs('code'));
    }
}
