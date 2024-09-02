<?php

namespace Appsumo_PLG_Licensing;

class Init
{
    public function __construct()
    {
        // up license table
        LicenseModel::up();

        // init webhooks
        new Webhooks\Init();

        // init callbacks
        new Callbacks\Init();
    }
}
