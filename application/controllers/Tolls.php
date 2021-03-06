<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/stripe/init.php';

class Tolls extends CI_Controller {
    // TODO: Inform user of payment of things via notification bar, also disable payment

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

    public function subscribe() {
        $vehicle_id = $this->session->userdata('vehicle_id');
        $plate = $this->session->userdata('plate');
        $email = $this->input->get_post('email');

        $insert_data = array(
            'vehicle_id' => $vehicle_id,
            'email' => $email
        );
        $this->db->insert('subscriptions', $insert_data); // TODO Check for existing subscription

        $this->db->where('vehicle_id',$vehicle_id);
        $results = $this->db->get('tolldata');
        if ($results->num_rows()<1) { // Check for vehicle details existing
            foreach ($this->session->userdata('tollcheck') as $tolldata_entry) {
                $insert_data = array(
                    'vehicle_id' => $vehicle_id,
                    'status' => $tolldata_entry['status'],
                    'admin_charge' => $tolldata_entry['admin_charge'],
                    'toll_charge' => $tolldata_entry['toll_charge'],
                    'motorway' => $tolldata_entry['motorway'],
                    'num_trips' => $tolldata_entry['num_trips']
                );
                $this->db->insert('tolldata', $insert_data);
            }
        }

        $this->load->view('header');
        $this->load->view('tolls_subscribe', array(
            'plate' => $plate,
            'email' => $email
        ));
        $this->load->view('footer');
    }

    public function email_receipt() {
        $charge_id = $this->input->get_post('charge_id');
        $email = $this->input->get_post('email');

        if ($charge_id===false || $email===false)
            redirect('/'); // TODO Check all errors

        try {
            $ch = \Stripe\Charge::retrieve($charge_id);
            $ch->receipt_email = $email;
            $ch->save();
        } catch (Exception $e) {
            $this->session->set_flashdata('receipt_email_status', 'error');
            redirect('/tolls/confirmation/'.$charge_id.'/');
        }

        $this->session->set_flashdata('receipt_email_status', 'success');
        redirect('/tolls/confirmation/'.$charge_id.'/');
    }

    public function confirmation($charge_id = null) {
        if (!$this->session->userdata('payment_total') || floatval($this->session->userdata('payment_total'))==0.5)
            redirect('/');

        $declined = true;
        $payment_total = "";

        if ($charge_id==null) {
            $stripeToken = $this->input->get_post('stripeToken');
            $idem = $this->input->get_post('idem');
            $payment_total = floatval($this->session->userdata('payment_total'));
            $total_cents = $payment_total * 100;

            try {
                $charge = \Stripe\Charge::create(array(
                    "amount" => $total_cents,
                    "currency" => "aud",
                    "source" => $stripeToken,
                    "description" => "mytolls.com Payment",
                    "statement_descriptor" => "mytolls.com Payment",
                    "metadata" => array(
                        "plate" => $this->session->userdata('plate'),
                        "state" => $this->session->userdata('state'),
                        "isBike" => $this->session->userdata('isBike')
                    )
                ), array(
                    "idempotency_key" => $idem,
                ));
                $declined = false;
                $charge_id = $charge->id;
            } catch (\Stripe\Error\Card $e) {
                $declined = true;
            } catch (Exception $e) {
                redirect('/');
            }
        } else {
            $declined = false;
        }

        if (!$declined) {
            mail('<SNIPPED>', 'Toll Payment '.$this->session->userdata('plate')." (".$this->session->userdata('state').")", "Payment Total: ".$payment_total.", Tolldata: ".var_export($this->session->userdata('tollcheck'),true));
            //$this->tollcheck->setProcessingStatus($this->session->userdata('plate'), $this->session->userdata('state'));
            $vehicles = new Dbo_vehicles();
            $vehicles->setProcessingDate($this->session->userdata('plate'), $this->session->userdata('state'));
        }

        $this->load->view('header');
        $this->load->view('tolls_confirmation', array(
            'payment_total' => $payment_total,
            'declined' => $declined,
            'charge_id' => $charge_id
        ));
        $this->load->view('footer');
    }

    public function pay() {
        if (!$this->session->userdata('toll_total') || $this->session->userdata('toll_total')==0.5)
            redirect('/');

        $toll_total_full = floatval(str_replace(",","",str_replace("$","",$this->tollcheck->totalTolls($this->session->userdata('tollcheck')))));
        $toll_total = floatval(str_replace(",","",str_replace("$","",$this->tollcheck->totalTolls($this->session->userdata('tollcheck'),true))));
        $service_fee = $this->tollcheck->calculateServiceFee($toll_total);
        $discount = $toll_total_full - $toll_total;
        $payment_total = round($toll_total + $service_fee, 2); // rounding again, just in case
        $this->session->set_userdata('payment_total', $payment_total);

        $this->load->view('header');
        $this->load->view('tolls_pay', array(
            'plate' => $this->session->userdata('plate'),
            'tollcheck' => $this->session->userdata('tollcheck'),
            'service_fee' => $service_fee,
            'payment_total' => $payment_total,
            'discount' => $discount
        ));
        $this->load->view('footer');
    }

	public function index() {
        $plate_raw = $this->input->get_post('plate');
        if ($plate_raw==false)
            $plate_raw = $this->session->userdata('plate');
        $plate = strtoupper(preg_replace("/[^a-zA-Z0-9]+/", "", $plate_raw));
        if ($plate==false || strlen($plate) > 9)
            redirect("/##");

        $state = $this->input->get_post('state');
        if ($state==false)
            $state = $this->session->userdata('state'); // TODO: THIS PROBABLY ISN'T WORKING

        if (!in_array($state, array("nsw","qld","vic","act","sa","nt","wa","tas")))
            redirect("/#");

        $vehicles = new Dbo_vehicles();
        $rego_details = $vehicles->get($plate, $state);
        $doPut = false;
        if (!$rego_details) {
            $rego_details = $this->tollcheck->getRegoDetails($plate, $state);
            $doPut = true;
        }
        if (strpos($rego_details[0],"MOTOR BIKE")!==false || strpos($rego_details[0],"MOTORBIKE")!==false || strpos($rego_details[0],"MOTOR CYCLE")!==false || strpos($rego_details[0],"MOTORCYCLE")!==false)
            $isBike = true;
        else
            $isBike = false;
        if ($doPut) {
            $vehicles->put($plate, $state, $rego_details, $isBike);
        }

        $tollcheck = $this->tollcheck->getAllTolls($plate, $state, $isBike);
        $toll_total = $this->tollcheck->totalTolls($tollcheck,true,true);

        $this->session->set_userdata(array(
            'plate' => $plate,
            'state' => $state,
            'isBike' => $isBike,
            'toll_total' => $toll_total,
            'tollcheck' => $tollcheck
        ));

        $this->load->view('header');
		$this->load->view('tolls',array(
            'rego_details' => $rego_details,
            'toll_total' => $toll_total,
            'plate' => $plate,
            'state' => $state,
            'isBike' => $isBike,
            'tollcheck' => $tollcheck
        ));
        $this->load->view('footer');
	}
}
