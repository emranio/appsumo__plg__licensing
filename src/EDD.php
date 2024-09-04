<?php

namespace Appsumo_PLG_Licensing;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Class EDD
|--------------------------------------------------------------------------
|
| This class handles the integration with Easy Digital Downloads (EDD) for 
| managing license purchases and revocations. It uses the EDD_Payment and 
| EDD_SL_License classes to create and manage payments and licenses.
|
*/
class EDD
{
    // The license object containing license details
    private $license_obj;

    // The user object representing the user associated with the license
    private $user;

    /**
     * Constructor to initialize the EDD class with a license object and user.
     *
     * @param object $license_obj The license object containing license details.
     * @param object|null $user The user object representing the user associated with the license. If null, it will be retrieved based on the user_id in the license object.
     */
    public function __construct($license_obj, $user = null)
    {
        // If the user is not provided, retrieve it based on the user_id in the license object
        $this->user = $user ?? get_user_by('id', $license_obj->user_id);
        // Set the license object
        $this->license_obj = $license_obj;
    }

    /**
     * Purchase method to create a new payment and license.
     *
     * @return int|false The payment ID if the purchase is successful, or false if it fails.
     */
    public function purchase()
    {
        // Create a new EDD_Payment object
        $payment = new \EDD_Payment();
        // Add the product to the payment with its variation ID and other details
        $payment->add_download($this->license_obj->product_id, [
            'quantity'    => 1,
            'price_id'    => $this->license_obj->variation_id,
            'tax'    => 0.00,
            'fees'    => array()
        ]);
        // Set the user ID and email for the payment
        $payment->user_id = $this->user->ID;
        $payment->email = $this->user->user_email;
        // Set the payment status to 'complete'
        $payment->status = 'complete';
        // Set the payment gateway to 'appsumo'
        $payment->gateway = 'appsumo';
        // Save the payment
        $payment->save();
        // Add a note to the payment with the license key. Add note before ->save() dows not work.
        $payment->add_note('Appsumo License key: ' . $this->license_obj->license_key);

        // If the payment is not created successfully, return false
        if (!$payment) {
            return false;
        }

        // Create a new EDD_SL_License object
        $license = new \EDD_SL_License();
        // Create the license with the product ID, payment ID, variation ID, and options
        $license->create(
            download_id: $this->license_obj->product_id,
            payment_id: $payment->ID,
            price_id: $this->license_obj->variation_id,
            options: [
                'is_lifetime' => true
            ]
        );

        // Prepare the payment data to pass to the 'appsumo_v2_edd_after_purchase' action
        $payment_data = [
            'price_id' => $this->license_obj->variation_id,
            'download_id' => $this->license_obj->product_id,
            'appsumo_license_key' => $this->license_obj->license_key,
            'payment_id' => $payment->ID
        ];
        
        // Trigger the 'appsumo_v2_edd_after_purchase' action with the payment data, user object, and a specific prefix
        do_action('appsumo_v2_edd_after_purchase', $payment_data, $this->user, '_gutenkit_' );

        // Return the payment ID
        return $payment->ID;
    }

    /**
     * Revoke method to deactivate or revoke a license.
     *
     * @return void
     */
    public function revoke()
    {
        // Log the deactivation action
        \write_log('deactivate method', $this->license_obj);

        // Retrieve the payment object based on the payment ID in the license object
        $payment = new \EDD_Payment($this->license_obj->payment_id);
        // Set the payment status to 'revoked'
        $payment->status = 'revoked';
        // Save the payment
        $payment->save();
        
        // Retrieve the payment object again based on the payment ID in the license object
        $payment = new \EDD_Payment($this->license_obj->payment_id);
        $args = array(
            'price_id' => $this->license_obj->variation_id,
        );
        // Remove the product from the payment (commented out)
        // $payment->remove_download($this->license_obj->product_id, $args);
        // Save the payment
        $payment->save();
    }
}