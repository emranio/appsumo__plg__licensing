<?php

namespace Appsumo_PLG_Licensing\Webhooks;

use Appsumo_PLG_Licensing\Env;
use Appsumo_PLG_Licensing\LicenseModel;

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
        $signature = $request->get_header('X-AppSumo-Signature');
        $payloadJson = $request->get_body();
        $secret = Env::get('appsumo_secret');
        $computedSignature = hash_hmac('sha256', $payloadJson, $secret);
        return hash_equals($signature, $computedSignature);
    }

    private function payload($key)
    {
        if (!$this->payload) {
            $this->payload = json_decode($request->get_body(), true);
        }
        return $payload[$key] ?? null;
    }

    private function response($message, $status = 200)
    {
        return new \WP_REST_Response(['message' => $message], $status);
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
        return $this->response('activated');
    }

    public function deactivate()
    {
        return $this->response('deactivated');
    }

    public function upgrade()
    {
        return $this->response('upgraded');
    }

    public function downgrade()
    {
        return $this->response('downgraded');
    }

    public function purchase()
    {
        return $this->response('purchased');
    }
}
