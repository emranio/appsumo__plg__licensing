<?php

namespace Appsumo_PLG_Licensing;

class Init
{
    public function __construct()
    {
        \write_log('Init class loaded', Env::get('version'));
        // up license table
        LicenseModel::up();
    }
}
