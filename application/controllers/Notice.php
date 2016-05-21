<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/stripe/init.php';

class Notice extends CI_Controller {
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

    public function index() {
        $this->load->view('header');
        $this->load->view('notice');
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
            redirect('/notice/confirmation/'.$charge_id.'/');
        }

        $this->session->set_flashdata('receipt_email_status', 'success');
        redirect('/notice/confirmation/'.$charge_id.'/');
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
                        "notice_number" => $this->session->userdata('notice_number'),
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
            mail('contact@<SNIPPED>', 'Toll Payment WITH NOTICE NUMBER '.$this->session->userdata('plate')." (".$this->session->userdata('state').")", "Payment Total: ".$payment_total.", Tolldata: ".var_export($this->session->userdata('tollcheck'),true));
        }

        $this->load->view('header');
        $this->load->view('notice_confirmation', array(
            'payment_total' => $payment_total,
            'declined' => $declined,
            'charge_id' => $charge_id
        ));
        $this->load->view('footer');
    }

    public function pay() {
        $notice_number = $this->input->get_post('notice_number');
        if ($notice_number==false)
            redirect("/");

        $this->session->set_userdata('notice_number', $this->input->get_post('notice_number'));

        $plate_raw = $this->input->get_post('plate');
        if ($plate_raw==false)
            redirect("/");
        $plate = strtoupper(preg_replace("/[^a-zA-Z0-9]+/", "", $plate_raw));
        if ($plate==false || strlen($plate) > 9)
            redirect("/");

        $state = $this->input->get_post('state');
        if ($state==false)
            redirect("/");

        if (!in_array($state, array("nsw","qld","vic","act","sa","nt","wa","tas")))
            redirect("/");

        $this->session->set_userdata('plate', $this->input->get_post('plate'));
        $this->session->set_userdata('state', $this->input->get_post('state'));

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

        $this->session->set_userdata('isBike', $isBike);

        $notice_details = $this->tollcheck->getNoticeDetails($notice_number, $plate, $state, $isBike);
        if (!$notice_details)
            redirect("/");
        $this->session->set_userdata('tollcheck', array($notice_details));

        $toll_total_full = floatval(str_replace(",","",str_replace("$","",$this->tollcheck->totalTolls(array($notice_details)))));
        $toll_total = floatval(str_replace(",","",str_replace("$","",$this->tollcheck->totalTolls(array($notice_details),true))));
        $service_fee = $this->tollcheck->calculateServiceFee($toll_total);
        $discount = $toll_total_full - $toll_total;
        $payment_total = round($toll_total + $service_fee, 2); // rounding again, just in case
        $this->session->set_userdata('payment_total', $payment_total);

        $this->load->view('header');
        $this->load->view('notice_pay', array(
            'notice_details' => $notice_details,
            'plate' => $this->session->userdata('plate'),
            'tollcheck' => $this->session->userdata('tollcheck'),
            'service_fee' => $service_fee,
            'payment_total' => $payment_total,
            'discount' => $discount
        ));
        $this->load->view('footer');
    }
}
