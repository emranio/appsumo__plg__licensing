<?php 
namespace Appsumo_PLG_Licensing;

class EDD{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    // purchase method
    public function purchase()
    {
        \write_log('purchase method', $this->model);
    }

    // deactivate method
    public function deactivate()
    {
        \write_log('deactivate method', $this->model);
    }

}