<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library('tollcheck');
    }

    public function run() {
        echo "Started at ".date(DATE_RFC2822).PHP_EOL;

        if (!$this->input->is_cli_request())
            die();

        $vehicles = array();

        $query = $this->db->query('SELECT DISTINCT vehicle_id FROM tolldata');

        foreach ($query->result() as $row) {
            $vehicles[] = $row->vehicle_id;
        }

        foreach ($vehicles as $vehicle_id) {
            $this->db->where('id', $vehicle_id);
            $vehicle_row = $this->db->get('vehicles')->row();
            $plate = $vehicle_row->plate;
            $state = $vehicle_row->state;
            $isBike = $vehicle_row->isBike;

            echo "Checking ".$plate." (".$state.")".PHP_EOL;

            $tollcheck = $this->tollcheck->getAllTolls($plate, $state, $isBike);

            $this->db->where('vehicle_id', $vehicle_id);
            $results = $this->db->get('tolldata');
            $tolldata_items = array();
            foreach ($results->result() as $tolldata_item) {
                $tolldata_items[] = $tolldata_item;
            }

            foreach ($tollcheck as $tollcheck_item) {
                $new_toll_flag = true;

                foreach ($tolldata_items as $tolldata_item) {
                    if ($tollcheck_item['motorway'] == $tolldata_item->motorway
                    && $tollcheck_item['status'] == $tolldata_item->status) {

                        $new_toll_flag = false;
                        break;
                    }
                }

                if ($new_toll_flag && $tollcheck_item['status']=="Unpaid") {
                    echo "Found new toll for vehicle ".$plate." (".$state.")".PHP_EOL;

                    $this->db->where('vehicle_id', $vehicle_id);
                    $results = $this->db->get('subscriptions');
                    foreach ($results->result() as $subscription) {
                        echo "Notifying ".$subscription->email.PHP_EOL;
                        $this->_emailNewToll($subscription->email, $tollcheck_item);
                    }

                    echo "Inserting new toll into database".PHP_EOL;
                    $insert_data = array(
                        'vehicle_id' => $vehicle_id,
                        'status' => $tollcheck_item['status'],
                        'admin_charge' => $tollcheck_item['admin_charge'],
                        'toll_charge' => $tollcheck_item['toll_charge'],
                        'motorway' => $tollcheck_item['motorway'],
                        'num_trips' => $tollcheck_item['num_trips']
                    );
                    $this->db->insert('tolldata', $insert_data);
                }
            }
        }

        echo "Finished at ".date(DATE_RFC2822).PHP_EOL.PHP_EOL;
    }

    private function _emailNewToll($email, $tollcheck_item) {
        $motorway = $tollcheck_item['motorway'];
        $toll_charge = "$".number_format($tollcheck_item['toll_charge'],2);
        $admin_charge = "$".number_format($tollcheck_item['admin_charge'],2);

        $headers   = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=iso-8859-1";
        $headers[] = "From: myTolls <support@mytolls.com>";
        $headers[] = "Bcc: Admin <<SNIPPED>>";
        $body = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="initial-scale=1.0"><meta name="format-detection" content="telephone=no">
        <title>[myTolls] New Toll Notification</title>
        <style type="text/css">/* Resets: see reset.css for details */
        .ReadMsgBody { width: 100%; background-color: #ebebeb;}
        .ExternalClass {width: 100%; background-color: #ebebeb;}
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
        body {-webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
        body {margin:0; padding:0;}
        table {border-spacing:0;}
        table td {border-collapse:collapse;}
        .yshortcuts a {border-bottom: none !important;}


        /* Constrain email width for small screens */
        @media screen and (max-width: 600px) {
            table[class="container"] {
                width: 95% !important;
            }
        }

        /* Give content more room on mobile */
        @media screen and (max-width: 480px) {
            td[class="container-padding"] {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
         }
        </style>
</head>
<body bgcolor="#fff" leftmargin="0" marginheight="0" marginwidth="0" style="margin:0; padding:10px 0;" topmargin="0">
<div><br />
<div style="width :600px;margin-left: auto;margin-right: auto;margin-bottom:15px"><img alt="Logo" src="https://mytolls.com/assets/img/logo_2x.png" /></div>

<table bgcolor="#fff" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
        <tbody>
                <tr>
                        <td align="center" bgcolor="#fff" style="background-color: #fff;" valign="top">
                        <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" class="container" style="border: 1px solid #dddddd;padding-top:10px;padding-bottom:10px" width="600">
                                <tbody>
                                        <tr>
                                                <td bgcolor="#ffffff" class="container-padding" style="background-color: #ffffff; padding-left: 30px; padding-right: 30px; font-size: 14px; line-height: 20px; font-family: Helvetica, sans-serif; color: #333;">&nbsp;
                                                <div class="body-text center" style="font-family: Arial,Helvetica,sans-serif, sans-serif;font-size: 15px;line-height: 21px;color: #444444;padding: 0 0px;line-height: 26px">
                                                <div style="font-size: 18px; line-height: 24px; color: #444444;font-weight: bold">myTolls has detected new toll for the ${motorway}</a></div>
                                                <br />
                                                <br />
                                                Hi,<br />
                                                <br />
                                                We have detected a new toll charge on the <b style="font-weight: 600">${motorway}</b>.<br />
                                                The toll charge is <b>${toll_charge}</b>.<br />
                                                The admin fee is <b>${admin_charge}</b> (discounts may be available).<br />
                                                <br />You can view all your paid and outstanding tolls here:<br />
                                                <br />
                                                <br />
                                                <a href="https://mytolls.com/tolls/" style="font-size:13px;font-weight:700;font-family:Helvetica,Arial,sans-serif;text-transform:uppercase;text-align:center;letter-spacing:1px;text-decoration:none;line-height:52px;display:block;width:250px;height:48px;border-top-left-radius:6px;border-top-right-radius:6px;border-bottom-right-radius:6px;border-bottom-left-radius:6px;
                                                 color: #ffffff;background-color: #6d5cae;border-color: #6d5cae;
                border: 1px solid;
                margin:0px auto" target="_blank" title="mytolls.com-Dashboard">My Tolls</a><br />
                                                <br />
                                                - myTolls Team<br />
                                                <br />
                                                <span style="font-size: 12px">Follow us on <a href="http://twitter.com/mytolls">Twitter</a> | Visit us on <a href="https://www.facebook.com/myTolls-128071830912970/">Facebook</a></span><br />
                                                <br />
                                                <em style="font-style:italic; font-size: 12px; color: #aaa;">A fresh new idea from Manic Services</em><br />
                                                &nbsp;</div>
                                                </td>
                                        </tr>
                                </tbody>
                        </table>
                        </td>
                </tr>
        </tbody>
</table>
</div>
</body>
</html>
EOT;
        mail($email, "[myTolls] New Toll Notification", $body, implode("\r\n", $headers));
    }
}
