<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/stripe/init.php';

class Admin extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('tollcheck');
        $this->load->model('Dbo_vehicles');
        $this->load->library('session');

        if (ENVIRONMENT=='production')
            \Stripe\Stripe::setApiKey("<SNIPPED>");
        else
            \Stripe\Stripe::setApiKey("<SNIPPED>");
    }

    public function processstripecharge() {
        // TODO: Validate Stripe from https://stripe.com/files/ips/ips_webhooks.json

        ob_start();
        echo "Begun Script".PHP_EOL;

        $input = @file_get_contents("php://input");
        $event_json = json_decode($input);
        $plate = $event_json->data->object->metadata->plate;
        $state = $event_json->data->object->metadata->state;
        $isBike = $event_json->data->object->metadata->isBike;
        if (isset($event_json->data->object->metadata->notice_number)) {
            mail('<SNIPPED>', 'Toll Payment for '.$plate.' requires manual processing (Notice Number)', '');
            exit;
        }
        $payment_total = $event_json->data->object->amount;
        $payment_total = $payment_total/100;
        if ($payment_total>300) {
            mail('<SNIPPED>', 'Toll Payment for '.$plate.' requires manual processing (Large Payment)', '');
            exit;
        }
        ignore_user_abort(true);
        set_time_limit(0);

        echo "Begun Processing".PHP_EOL;

        header('Connection: close');
        header('Content-Length: '.ob_get_length());
        ob_end_flush();
        ob_flush();
        flush();

        $roamAmount = $this->tollcheck->processRoamTolls($plate, $state, $isBike, $payment_total);
        $m5Amount = $this->tollcheck->processM5Tolls($plate, $state, $payment_total-$roamAmount);

        mail('<SNIPPED>', 'Completed Processing', $roamAmount.", ".$m5Amount);

        if ($roamAmount+$m5Amount>1) {
            mail('<SNIPPED>', 'Toll Payment for '.$plate.' successful','Roam Amount: '.$roamAmount.", M5 Amount: ".$m5Amount);
            $bt = \Stripe\BalanceTransaction::retrieve($event_json->data->object->balance_transaction);
            $ch = \Stripe\Charge::retrieve($event_json->data->object->id);

            $ch->metadata = array(
                'plate' => $event_json->data->object->metadata->plate,
                'state' => $event_json->data->object->metadata->state,
                'isBike' => $event_json->data->object->metadata->isBike,
                'roamAmount' => $roamAmount,
                'm5Amount' => $m5Amount,
                'rawProfit' => $payment_total-$roamAmount-$m5Amount-(floatval($bt->fee)/100)
            );
            $ch->save();
        } else {
            mail('<SNIPPED>', 'Toll Payment for '.$plate.' ERROR (No Amounts)','');
        }
    }
}
