<?php

namespace Appsumo_PLG_Licensing;

class EDD
{
    private $license_obj;
    private $user;

    public function __construct($license_obj, $user = null)
    {
        $this->user = $user ?? get_user_by('id', $license_obj->user_id);
        $this->license_obj = $license_obj;
    }

    // purchase method
    public function purchase()
    {
        $payment = new \EDD_Payment();
        $payment->add_download($this->license_obj->product_id, [
            'quantity'    => 1,
            'price_id'    => $this->license_obj->variation_id,
            // 'item_price'  => 0.00,
            'tax'    => 0.00,
            'fees'    => array()
        ]);
        $payment->user_id = $this->user->ID;
        $payment->email = $this->user->user_email;
        $payment->status = 'complete';
        $payment->gateway = 'appsumo';
        $payment->save();
        $payment->add_note('Appsumo License key: ' . $this->license_obj->license_key);

        if (!$payment) {
            return false;
        }

        $license = new \EDD_SL_License();
        $license->create(
            download_id: $this->license_obj->product_id,
            payment_id: $payment->ID,
            price_id: $this->license_obj->variation_id,
            options: [
                'is_lifetime' => true
            ]
        );

        return $payment->ID;
    }

    // deactivate/ revoke method
    public function revoke()
    {
        \write_log('deactivate method', $this->license_obj);

        $payment = new \EDD_Payment($this->license_obj->payment_id);
        $payment->status = 'revoked';
        $payment->save();
        
        $payment = new \EDD_Payment($this->license_obj->payment_id);
        $args = array(
            'price_id' => $this->license_obj->variation_id,
        );
        // $payment->remove_download($this->license_obj->product_id, $args);
        $payment->save();
        
        
    }
}
