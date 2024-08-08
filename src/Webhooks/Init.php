<?php

namespace Appsumo_PLG_Licensing\Webhooks;

use Appsumo_PLG_Licensing\Env;
use Appsumo_PLG_Licensing\LicenseModel;
use Appsumo_PLG_Licensing\Util;

class Init
{
    private $payload;

    private $allowedEvents = ['activate', 'deactivate', 'upgrade', 'downgrade', 'purchase'];

    public function __construct()
    {
        // Register the webhook routes
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('appsumo_plg_licensing/v2', '/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_request'],
            'permission_callback' => '__return_true',
        ]);
    }

    private function verify_signature($request)
    {
        $this->payload = json_decode($request->get_body(), true);

        return true;
        $signature = $request->get_header('X-AppSumo-Signature');
        $payloadJson = $request->get_body();
        $secret = Env::get('appsumo_secret');
        $computedSignature = hash_hmac('sha256', $payloadJson, $secret);
        return hash_equals($signature, $computedSignature);
    }

    private function payload($key)
    {
        return $this->payload[$key] ?? null;
    }

    private function response($message, $status = 200)
    {
        return new \WP_REST_Response([
            'success' => ($status === 200), 
            'message' => $message
        ], $status);
    }

    public function handle_request(\WP_REST_Request $request)
    {
        if (!$this->verify_signature($request)) {
            return $this->response('Invalid signature', 403);
        }

        $event = $this->payload('event') ?? 'unknown';
        // if event is unknown, return 400
        if (!in_array($event, $this->allowedEvents)) {
            return $this->response('Invalid event', 400);
        }

        // call the event handler method
        return $this->$event();
    }

    public function activate()
    {
        $prev_license = LicenseModel::where('license_key', $this->payload('license_key'))->first();

        if ($prev_license) {
            return $this->response('license_key already in use', 400);
        }

        $this->payload['product_id'] = Env::get('product_id');
        $this->payload['variation_id'] = Util::variation_id_by_tier($this->payload('tier'));
        LicenseModel::create($this->payload);

        return $this->response('activated');
    }

    public function deactivate()
    {
        LicenseModel::where('license_key', $this->payload('license_key'))->update(['license_status' => 'deactivated']);
        return $this->response('deactivated');
    }

    public function upgrade()
    {
        $prev_license = LicenseModel::where('license_key', $this->payload('license_key'))->first();

        if ($prev_license) {
            return $this->response('license_key already in use', 400);
        }

        $prev_license = LicenseModel::where('license_key', $this->payload('prev_license_key'))->first();

        if (!$prev_license) {
            return $this->response('prev_license_key not found', 400);
        }

        $this->payload['user_id'] = $prev_license->user_id;
        $this->payload['product_id'] = $prev_license->product_id;
        $this->payload['variation_id'] = Util::variation_id_by_tier($this->payload('tier'));
        $this->payload['license_status'] = 'active';
        LicenseModel::create($this->payload);

        return $this->response('upgraded');
    }

    public function downgrade()
    {
        $prev_license = LicenseModel::where('license_key', $this->payload('license_key'))->first();

        if ($prev_license) {
            return $this->response('license_key already in use', 400);
        }

        $prev_license = LicenseModel::where('license_key', $this->payload('prev_license_key'))->first();

        if (!$prev_license) {
            return $this->response('prev_license_key not found', 400);
        }

        $this->payload['user_id'] = $prev_license->user_id;
        $this->payload['product_id'] = $prev_license->product_id;
        $this->payload['variation_id'] = Util::variation_id_by_tier($this->payload('tier'));
        $this->payload['license_status'] = 'active';
        LicenseModel::create($this->payload);

        return $this->response('downgraded');
    }

    public function purchase()
    {
        // do nothing
        return $this->response('purchased');
    }
}
