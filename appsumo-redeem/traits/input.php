<?php

namespace Appsumo_Redeem\Traits;


trait Input {
    public $inputs;

    public function set($inputs = []){
        $this->inputs = $inputs;
        return $this;
    }

    public function get($name = null){
        if($name == null){
            return $this->inputs;
        }
        return isset($this->inputs[$name]) ? $this->inputs[$name] : '';
    }
}