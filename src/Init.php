<?php

namespace Appsumo_PLG_Licensing;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Class Init
|--------------------------------------------------------------------------
|
| This class initializes the Appsumo PLG Licensing plugin. It sets up the 
| license table, initializes webhooks, and initializes callbacks.
|
*/
class Init
{
    /**
     * Constructor to initialize the plugin.
     *
     * This constructor performs the following actions:
     * - Sets up the license table by calling the `up` method of `LicenseModel`.
     * - Initializes webhooks by creating an instance of `Webhooks\Init`.
     * - Initializes callbacks by creating an instance of `Callbacks\Init`.
     */
    public function __construct()
    {
        // Set up the license table
        LicenseModel::up();

        // Initialize webhooks
        new Webhooks\Init();

        // Initialize callbacks
        new Callbacks\Init();
    }
}