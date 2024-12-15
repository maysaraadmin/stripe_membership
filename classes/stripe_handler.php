<?php
namespace enrol_stripe_membership;

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use Stripe\Stripe;
use Stripe\Checkout\Session;

class stripe_handler {
    public static function create_checkout_session($userid, $priceid, $successurl, $cancelurl) {
        global $CFG;

        Stripe::setApiKey(get_config('enrol_stripe_membership', 'secretkey'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceid,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successurl,
            'cancel_url' => $cancelurl,
        ]);

        return $session->url;
    }
}