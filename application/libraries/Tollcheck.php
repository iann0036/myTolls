<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once "<SNIPPED>/application/third_party/simple_html_parser.php";

function sortTollsByDateInline($a, $b) {
    if ($a['status']=="Paid" && $b['status']=="Unpaid")
        return true;
    else if ($b['status']=="Paid" && $a['status']=="Unpaid")
        return false;
    return ($a['timestamp'] < $b['timestamp']);
}

class Tollcheck {
    private function _convert_datetime($datetime, $dateonly = false) {
        $newdate2 = $_SERVER['REQUEST_TIME']-$datetime;
        if ($newdate2>172799 || $dateonly)
            return date("F j, Y",$datetime);
        else if ($newdate2>86399)
            return "Yesterday";
        else if ($newdate2>7199)
            return intval($newdate2/3600)." hours ago";
        else if ($newdate2>3599)
            return "1 hour ago";
        else if ($newdate2>119)
            return intval($newdate2/60)." minutes ago";
        else if ($newdate2>59)
            return "1 minute ago";
        else if ($newdate2>=0)
            return "Just a moment ago";
        else
            return "In the future";
    }

    public function calculateServiceFee($total) {
        return ((intval(round($total * 0.029, 2)*110)/100) + 0.33); // (2.9% + 0.30) * 0.1 (GST)
    }

    public function calculatedDiscountedFee($discounted_admin, $full_admin) {
        $minimum = 0.55;

        if ($discounted_admin+$minimum>$full_admin)
            return $discounted_admin+$minimum;
        return $discounted_admin + (intval(($full_admin-$discounted_admin)*0.3*100)/100);
    }

    public function toNumber($str) {
        return floatval(trim(str_replace(",", "", str_replace("$", "", $str))));
    }

    public function toCurrency($str) {
        $num = $this->toNumber($str);
        if ($num<0)
            return "-$".number_format($num, 2);
        return "$".number_format($num, 2);
    }

    public function processRoamTolls($plate, $state, $isBike, $payment_total) {
        if ($isBike=="false")
            $isBike = false;
        if ($isBike=="true")
            $isBike = true;

        if ($state=="act") { $state = 1; }
        if ($state=="nsw") { $state = 2; }
        if ($state=="vic") { $state = 3; }
        if ($state=="qld") { $state = 4; }
        if ($state=="sa") { $state = 5; }
        if ($state=="wa") { $state = 6; }
        if ($state=="tas") { $state = 7; }
        if ($state=="nt") { $state = 8; }

        $all_tolls = $this->getAllTolls($plate,$state,$isBike);

        $no_unpaid = true;
        foreach ($all_tolls as $toll) {
            if ($toll['status']=="Unpaid") {
                $no_unpaid = false;
            }
        }
        if ($no_unpaid) {
            echo "No unpaid found";
            exit;
        }

        $cookiefile = '<SNIPPED>/cookies_'.uniqid().'.txt';
        file_put_contents($cookiefile, '');

        //extract data from the post
        //set POST variables
        $url = 'https://secure.roamexpress.com.au/tollpayments/Search.asp';

        //open connection
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        //pre-exec
        curl_exec($ch);

        $fields = array(
            'txtLPN' => urlencode($plate),
            'cboStateRegistered' => urlencode($state),
            'txtTollNotice' => urlencode(''),
            'hidDefaultColor' => urlencode('Black'),
            'hidErrorColor' => urlencode('red'),
            'hidState' => urlencode('Search'),
            'hidPath' => urlencode('/secure/tollpayments/default.asp'),
            'hidRefNo' => urlencode(''),
            'hidPaymentMethod' => urlencode('0'),
            'hidTotalAdmFee' => urlencode('0'),
            'hidTotalAdmToll' => urlencode('0'),
            'hidTotalAdmPaid' => urlencode('0'),
            'hidActive' => urlencode('0'),
            'hidTotalAdmBeforeDiscount' => urlencode('0'),
            'hidDateFrom' => urlencode(''),
            'hidDateTo' => urlencode(''),
            'hidMotorbike' => urlencode('0'),
            'hidLPNWildcardSearch' => urlencode('0'),
            'hidStateRegistered' => urlencode(''),
            'hidTollMotorwayIDString' => urlencode('1=102,2=60,3=107,6=100,7=108,8=111,9=201,12=76,13=280,14=222'),
            'hidTollMaxBackDaysString' => urlencode('1=20140108,2=20140108,3=20140108,6=20140108,7=20140108,8=20140108,9=20140108,12=20140108,13=20140108,14=20140108'),
            'hidMotorwayText' => urlencode(''),
            'hidMotorwayIndex' => urlencode(''),
            'hidMotorwayId' => urlencode(''),
            'hidDebtCollection' => urlencode('0')
        );

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        $url = "https://secure.roamexpress.com.au/tollpayments/PaymentDetails.asp";

        $fields = array(
            'txtLPN' => urlencode(''),
            'cboStateRegistered' => urlencode(''),
            'txtTollNotice' => urlencode(''),
            //'chk' => urlencode('AT88QT|D10987975264|3/01/2016 7:43:55 PM|109|LCE|Notice Sent (1A): A Toll Notice (1A) has been sent the vehicle owner requesting payment. To avoid any further Administration Fees tick Pay Now to pay this outstanding Toll.|LongDesc|60=0,76=0,100=1.1,102=1.1,107=1.1,108=1.1,111=1.1,201=0,222=1.1,280=0|10|3.17|718136246|True|False|10|85017078|1|False|False|False|60=X0.00_V0.55,76=X0.00,100=X0.00_V0.55_E1.10,102=X0.00_V0.55_E1.10,107=X0.00_V0.55_E1.10,108=X0.00_V0.55_E1.10,111=X0.00_V0.55_E1.10,201=X0.00,222=X0.00_V0.55_E1.10,280=X0.00|False|divAAF4|divNAAF4|divTOLL4'),
            'radPayment' => urlencode('debit'),
            'cboMotorwayACC' => urlencode('1'),
            'hidDefaultColor' => urlencode('Black'),
            'hidErrorColor' => urlencode('red'),
            'hidState' => urlencode('NextDetails'),
            'hidPath' => urlencode('/secure/tollpayments/default.asp'),
            //'hidRefNo' => urlencode('$AT88QT|D10987975264|3/01/2016 7:43:55 PM|109|LCE|Notice Sent (1A): A Toll Notice (1A) has been sent the vehicle owner requesting payment. To avoid any further Administration Fees tick Pay Now to pay this outstanding Toll.|LongDesc|60=0,76=0,100=1.1,102=E1.1,107=1.1,108=1.1,111=1.1,201=0,222=1.1,280=0,|10|3.17|718136246|True|False|10|85017078|1|False|False|False|60=X0.00_V0.55,76=X0.00,100=X0.00_V0.55_E1.10,102=X0.00_V0.55_E1.10,107=X0.00_V0.55_E1.10,108=X0.00_V0.55_E1.10,111=X0.00_V0.55_E1.10,201=X0.00,222=X0.00_V0.55_E1.10,280=X0.00|False|divAAF4|divNAAF4|divTOLL4|'),
            'hidPaymentMethod' => urlencode('15'),
            //'hidTotalAdmFee' => urlencode('1.1'),
            //'hidTotalAdmToll' => urlencode('3.17'),
            //'hidTotalAdmPaid' => urlencode('4.27'),
            'hidActive' => urlencode('1'),
            //'hidTotalAdmBeforeDiscount' => urlencode('1.1'),
            'hidDateFrom' => urlencode(''),
            'hidDateTo' => urlencode(''),
            'hidMotorbike' => urlencode('0'),
            'hidLPNWildcardSearch' => urlencode('0'),
            'hidStateRegistered' => urlencode(''),
            'hidTollMotorwayIDString' => urlencode('1=102,2=60,3=107,6=100,7=108,8=111,9=201,12=76,13=280,14=222'),
            'hidTollMaxBackDaysString' => urlencode('1=20140118,2=20140118,3=20140118,6=20140118,7=20140118,8=20140118,9=20140118,12=20140118,13=20140118,14=20140118'),
            'hidMotorwayText' => urlencode('Roam Express'),
            'hidMotorwayIndex' => urlencode('1'),
            'hidMotorwayId' => urlencode('102'),
            'hidDebtCollection' => urlencode('0')
        );

        $totaladmfee = 0;
        $totaladmtoll = 0;
        $hidRefNo = "";

        foreach ($all_tolls as $toll) {
            if ($toll['timestamp'] < strtotime("-725 days") && $toll['status']=="Unpaid" && $toll['toll_provider']=="Roam") {
                mail('<SNIPPED>', 'Toll Payment for '.$plate.' MANUAL PROCESSING REQUIRED (Older than 2 yrs)','');
            } elseif ($toll['toll_provider']=="Roam" && $toll['status']=="Unpaid") {
                $hidRefNo .= "$";
                $hidRefNo .= $toll['details_raw'];
                $hidRefNo .= "|";
                $totaladmfee += $this->toNumber($toll['discounted_admin_charge']);
                $totaladmtoll += $this->toNumber($toll['toll_charge']);
            }
        }

        if ($totaladmtoll==0)
            return 0;

        if (($totaladmfee+$totaladmtoll)>$this->toNumber($payment_total)) {
            mail('<SNIPPED>', 'Toll Payment for '.$plate.' ERROR (Too High Tolls)',($totaladmfee+$totaladmtoll)." > ".$this->toNumber($payment_total));
            die();
        }

        $fields['hidRefNo'] = $hidRefNo;
        $fields['hidTotalAdmFee'] = $totaladmfee;
        $fields['hidTotalAdmBeforeDiscount'] = $totaladmfee;
        $fields['hidTotalAdmToll'] = $totaladmtoll;
        $fields['hidTotalAdmPaid'] = $totaladmfee+$totaladmtoll;

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        $url = "https://secure.roamexpress.com.au/tollpayments/PaymentConfirmation.asp";

        $fields = array(
            'txtContactName' => urlencode('Mytolls Admin'),
            'txtContactEmail' => urlencode('admin@mytolls.com'),
            'txtContactPhone1' => urlencode('04'),
            'txtContactPhone2' => urlencode('<SNIPPED>'),
            'cboMotorwayACC' => urlencode('1'),
            'txtAccountNumber' => urlencode('<SNIPPED>'),
            'txtTagNumber' => urlencode('<SNIPPED>'),
            'hidDefaultColor' => urlencode('black'),
            'hidErrorColor' => urlencode('red'),
            'hidState' => urlencode('NextConfirm'),
            'hidActive' => urlencode('1'),
            'hidChequeNumber' => urlencode(''),
            'hidMoneyOrderNumber' => urlencode(''),
            'hidBPayReferenceNumber' => urlencode(''),
            'hidCardNumber' => urlencode(''),
            'hidPhone' => urlencode('<SNIPPED>'),
            'hidCardTypeText' => urlencode(''),
            'hidExpired' => urlencode(''),
            'hidCVV' => urlencode(''),
            'hidMotorwayText' => urlencode('Roam Express'),
            'hidSurcharge' => urlencode(''),
            'hidSurchargeText' => urlencode(''),
            'hidMotorwayId' => urlencode('102'),
            'hidDisputeReason' => urlencode(''),
            'hidDisputeOption' => urlencode(''),
            'hidDebtCollection' => urlencode('0')
        );

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        $startpos = strpos($result,"<h4>Total Charge: $") + 19;
        $endpos = strpos($result,"</h4>",$startpos);
        $total_charge = $this->toNumber(substr($result,$startpos,$endpos-$startpos));

        if (!is_numeric($total_charge) || $this->toNumber($payment_total)<$total_charge) {
            mail("<SNIPPED>","ERROR TOO MUCH ROAM TOLL - Total Charge = ".$total_charge,$result);
            exit;
        }

        $url = 'https://secure.roamexpress.com.au/tollpayments/PaymentReceiptProcess.asp?hidState=NextReceipt';

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, false);

        $result = curl_exec($ch);

        $url = 'https://secure.roamexpress.com.au/tollpayments/PaymentReceipt.asp';

        curl_setopt($ch,CURLOPT_URL, $url);

        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        // TODO: Remember to ignore processing and alert for >2 yrs old [DONE - not tested]

        return $total_charge;
    }

    public function totalTolls($tollcheck, $discountedfees = false, $includetransactionfee = false) {
        $total = 0.00;
        foreach ($tollcheck as $toll_entry) {
            if ($toll_entry['status'] == "Unpaid") {
                if ($discountedfees)
                    $total += $this->toNumber($toll_entry['discounted_total_price']);
                else
                    $total += $this->toNumber($toll_entry['total_price']);
            }
        }

        if ($includetransactionfee)
            $total += $this->calculateServiceFee($total);

        return $total;
    }

    public function getNoticeDetails($notice_number, $plate, $state = '2', $isBike = false) {
        // TODO Make threaded

        $sht_toll = $this->_getHarbourTolls($plate, $notice_number, 'SHT');
        if ($sht_toll) {
            return $sht_toll;
        }
        $shb_toll = $this->_getHarbourTolls($plate, $notice_number, 'SHB');
        if ($shb_toll) {
            return $shb_toll;
        }

        $details = $this->_getRoamTolls($plate,$state,$isBike,$notice_number);
        if (count($details)>0) {
            if ($details[0]['status']=="Unpaid")
                return $details[0];
        }

        // TODO: M5 Toll Notice Number

        return false;
    }

    private function _getHarbourTolls($plate, $notice_number, $toll_road = "SHT") {
        if ($toll_road=="SHT") {
            $toll_road_name = "Sydney Harbour Tunnel";
        } else {
            $toll_road_name = "Sydney Harbour Bridge";
        }

        $cookiefile = '<SNIPPED>/cookies_'.uniqid().'.txt';
        file_put_contents($cookiefile, '');

        //extract data from the post
        //set POST variables
        $url = 'https://myrta.com/myEToll/respondToTollNotice.do';

        //open connection
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $fields = array(
            'action' => urlencode('retrieveTollNotice'),
            'numberPlate' => urlencode($plate),
            'tollNoticeNumber' => urlencode($notice_number),
            'tollRoad' => $toll_road
        );

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        if (strpos($result,"payment method")===false) {
            return false;
        }

        $fields = array(
            'action' => 'submitPaymentMethod',
            'paymentMethod' => 'TAG',
            'tagType' => '102',
            'tagAccountNumber' => '<SNIPPED>',
            'tagNumber' => '<SNIPPED>',
            'creditCardType' => '',
            'creditCardNumber' => '',
            'creditCardExpiryMonth' => '',
            'creditCardExpiryYear' => '',
            'creditCardCvc' => '',
            'creditCardHolder' => '',
            'givenName' => '',
            'surname' => '',
            'company' => '',
            'address1' => '',
            'address2' => '',
            'suburb' => '',
            'postcode' => '',
            'state' => '',
            'phoneNumber' => ''
        );

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

        //execute post
        $result = curl_exec($ch);

        $dom = str_get_html($result);

        //#form > table > tbody > tr:nth-child(4) > td.displayText
        $trs = $dom->find('#form', 0)->first_child()->find('tr');
        $toll_charge = $this->toNumber(trim($trs[3]->last_child()->plaintext));
        $admin_charge = 0;
        $discounted_admin_charge = 0;
        $discounted_total_price = $toll_charge;
        $total_price = $toll_charge;

        $result_entry = array(
            'status' => 'Unpaid',
            'system_data' => '',
            'discounted_admin_charge' => $discounted_admin_charge,
            'rego' => $plate,
            'notice_text' => "",
            'admin_charge' => $admin_charge,
            'toll_charge' => $toll_charge,
            'details_raw' => null,
            'time' => "<i>Unknown</i>",
            'datetime' => "Unknown",
            'timestamp' => null,
            'discounted_total_price' => $discounted_total_price,
            'total_price' => $total_price,
            'num_trips' => 1,
            'motorway' => $toll_road_name,
            'notice_number' => $notice_number,
            'toll_provider' => 'RMS'
        );

        curl_close($ch);

        return $result_entry;
    }

    private function _getRoamTolls($plate, $state, $isBike, $notice_number = false) {
        if ($state=="act") { $state = 1; }
        if ($state=="nsw") { $state = 2; }
        if ($state=="vic") { $state = 3; }
        if ($state=="qld") { $state = 4; }
        if ($state=="sa") { $state = 5; }
        if ($state=="wa") { $state = 6; }
        if ($state=="tas") { $state = 7; }
        if ($state=="nt") { $state = 8; }

        $cookiefile = '<SNIPPED>/cookies_'.uniqid().'.txt';
        file_put_contents($cookiefile, '');

        //extract data from the post
        //set POST variables
        $url = 'https://secure.roamexpress.com.au/tollpayments/Search.asp';

        //open connection
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        //pre-exec
        curl_exec($ch);

        $fields = array(
            'txtLPN' => urlencode($plate),
            'cboStateRegistered' => urlencode($state),
            'txtTollNotice' => urlencode(''),
            'hidDefaultColor' => urlencode('Black'),
            'hidErrorColor' => urlencode('red'),
            'hidState' => urlencode('Search'),
            'hidPath' => urlencode('/secure/tollpayments/default.asp'),
            'hidRefNo' => urlencode(''),
            'hidPaymentMethod' => urlencode('0'),
            'hidTotalAdmFee' => urlencode('0'),
            'hidTotalAdmToll' => urlencode('0'),
            'hidTotalAdmPaid' => urlencode('0'),
            'hidActive' => urlencode('0'),
            'hidTotalAdmBeforeDiscount' => urlencode('0'),
            'hidDateFrom' => urlencode(''),
            'hidDateTo' => urlencode(''),
            'hidMotorbike' => urlencode('0'),
            'hidLPNWildcardSearch' => urlencode('0'),
            'hidStateRegistered' => urlencode(''),
            'hidTollMotorwayIDString' => urlencode('1=102,2=60,3=107,6=100,7=108,8=111,9=201,12=76,13=280,14=222'),
            'hidTollMaxBackDaysString' => urlencode('1=20140108,2=20140108,3=20140108,6=20140108,7=20140108,8=20140108,9=20140108,12=20140108,13=20140108,14=20140108'),
            'hidMotorwayText' => urlencode(''),
            'hidMotorwayIndex' => urlencode(''),
            'hidMotorwayId' => urlencode(''),
            'hidDebtCollection' => urlencode('0')
        );

        if ($isBike)
            $fields['hidMotorbike'] = urlencode('1');

        if ($notice_number)
            $fields['txtTollNotice'] = urlencode($notice_number);

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        $dom = str_get_html($result);

        $result = array();

        try {
            if ($dom==null) { mail('<SNIPPED>', 'ERROR: No Table Area', $result); redirect('/'); }
            $table_area = $dom->find('#Table3', 0);
            if ($table_area==null) { mail('<SNIPPED>', 'ERROR: No Table Area2', $result); redirect('/'); }
            $table = $table_area->find('table', 0);
            if ($table==null) { mail('<SNIPPED>', 'ERROR: No Table Area3', $result); redirect('/'); }
            $entries = $table->find('tr');
            if ($entries==null) { mail('<SNIPPED>', 'ERROR: No Table Area4', $result); redirect('/'); }

            for ($i = 1; $i < count($entries); $i++) {
                $result_entry = array();
                $result_entry['notice_number'] = $notice_number;
                $tds = $entries[$i]->find('td');
                $result_entry['status'] = "Paid";

                if (count($tds)>8) {
                    $result_entry['rego'] = trim($tds[1]->plaintext);
                    $result_entry['notice_text'] = trim($tds[5]->plaintext);
                    $result_entry['admin_charge'] = $this->toNumber(trim($tds[6]->plaintext));
                    if (strlen($result_entry['admin_charge']) < 1)
                        $result_entry['admin_charge'] = 0;
                    $result_entry['toll_charge'] = $this->toNumber(trim($tds[7]->plaintext));
                    if (strlen($result_entry['toll_charge']) < 1)
                        $result_entry['toll_charge'] = 0;
                } else {
                    $result_entry['rego'] = trim($tds[1]->plaintext);
                    $result_entry['notice_text'] = trim($tds[2]->plaintext);
                    $result_entry['admin_charge'] = $this->toNumber(trim($tds[3]->plaintext));
                    if (strlen($result_entry['admin_charge']) < 1)
                        $result_entry['admin_charge'] = 0;
                    $result_entry['toll_charge'] = $this->toNumber(trim($tds[4]->plaintext));
                    if (strlen($result_entry['toll_charge']) < 1)
                        $result_entry['toll_charge'] = 0;
                }

                $payment_input = $tds[0]->find('input', 0);
                if ($payment_input != null) {
                    $result_entry['details_raw'] = $payment_input->value;
                    $startpos = strpos($result_entry['details_raw'], ",100=");
                    $endpos = strpos($result_entry['details_raw'], ",", $startpos+4);
                    $discounted_admin_raw = str_replace("E","",substr($result_entry['details_raw'], $startpos+5, $endpos-$startpos-5));
                    if (is_numeric($discounted_admin_raw)) {
                        $full_admin_raw = $this->toNumber($result_entry['toll_charge']);
                        $new_discounted_admin = $this->calculatedDiscountedFee($this->toNumber($discounted_admin_raw), $this->toNumber($full_admin_raw));
                        $result_entry['discounted_admin_charge'] = $new_discounted_admin;
                    } else {
                        mail('<SNIPPED>', 'Debug Point G', '');
                        $result_entry['discounted_admin_charge'] = $result_entry['admin_charge'];
                    }
                    $result_entry['status'] = "Unpaid";
                    $details_parts = explode("|", $result_entry['details_raw']);
                    $result_entry['time'] = $this->_convert_datetime(DateTime::createFromFormat('j/m/Y g:i:s A', $details_parts[2])->getTimestamp());
                    $result_entry['datetime'] = date('g:i A j/m/Y', DateTime::createFromFormat('j/m/Y g:i:s A', $details_parts[2])->getTimestamp());
                    $result_entry['timestamp'] = DateTime::createFromFormat('j/m/Y g:i:s A', $details_parts[2])->getTimestamp();
                    if (DateTime::createFromFormat('j/m/Y g:i:s A', $details_parts[2])->getTimestamp() < strtotime("-725 days")) {
                        $result_entry['discounted_admin_charge'] = $result_entry['admin_charge']+(0.05*($result_entry['admin_charge']+$result_entry['toll_charge'])); // offset to not hit trigger
                    }
                } else {
                    $result_entry['discounted_admin_charge'] = $this->toNumber(trim($tds[3]->plaintext));
                    $result_entry['details_raw'] = null;
                    $result_entry['time'] = "<i>Unknown</i>";
                    $result_entry['datetime'] = "Unknown";
                    $result_entry['timestamp'] = null;
                }

                $result_entry['total_price'] = $this->toNumber($result_entry['admin_charge']) + $this->toNumber($result_entry['toll_charge']);
                $result_entry['discounted_total_price'] = $this->toNumber($result_entry['discounted_admin_charge']) + $this->toNumber($result_entry['toll_charge']);

                if ($result_entry['admin_charge'] == 0)
                    $result_entry['admin_charge'] = null;

                if (count($tds)>8) {
                    $result_entry['num_trips'] = 1;
                    $motorway_parts = explode(" - ",trim($tds[4]->plaintext));
                    $result_entry['motorway'] = $motorway_parts[0];
                } else {
                    $text_parts = explode(" toll(s) have been found for trip(s) by this vehicle on the ", $result_entry['notice_text']);
                    $result_entry['num_trips'] = $text_parts[0];
                    $text_parts = explode(" where ", array_pop($text_parts));
                    $text_parts = explode("on the ", $text_parts[0]);
                    $text_parts = explode(".", array_pop($text_parts));
                    $result_entry['motorway'] = trim($text_parts[0]);
                    if (strlen($result_entry['motorway']) < 2)
                        $result_entry['motorway'] = "Unknown";
                    $text_parts = explode(" ", $result_entry['num_trips']);
                    $result_entry['num_trips'] = intval($text_parts[0]);
                }

                if (strpos($result_entry['notice_text'],"where a charge request will be made to your Tag provider as requested")!==false) {
                    $result_entry['status'] = "Paid";
                }

                $result_entry['toll_provider'] = "Roam";

                $result[] = $result_entry;
            }
        } catch (Exception $e) {
            mail('<SNIPPED>', 'ERROR: Exception paying Roam for '.$plate,$e->getMessage());
            mail('<SNIPPED>', 'ERROR: Exception paying Roam for '.$plate,$e->getTraceAsString());
            return array();
        }

        return $result;
    }

    public function getAllTolls($plate, $state = '2', $isBike = false) {
        // TODO: Make threaded

        $result = $this->_getRoamTolls($plate, $state, $isBike);

        $m5toll = $this->_getM5Toll($plate, $state);
        if ($m5toll!=false)
            $result[] = $m5toll;

        // TODO: Check all values for minimum toll and admin value - sanity checks

        usort($result, "sortTollsByDateInline");

        return $result;
    }

    private function _getM5Toll($plate, $state) {
        if ($state==1) { $state = "ACT"; }
        if ($state==2) { $state = "NSW"; }
        if ($state==3) { $state = "VIC"; }
        if ($state==4) { $state = "QLD"; }
        if ($state==5) { $state = "SA"; }
        if ($state==6) { $state = "WA"; }
        if ($state==7) { $state = "TAS"; }
        if ($state==8) { $state = "NT"; }

        $cookiefile = '<SNIPPED>/cookies_'.uniqid().'.txt';
        file_put_contents($cookiefile, '');

        //extract data from the post
        //set POST variables
        $url = 'https://webpayments.m5motorway.com.au/search/do';

        //open connection
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $fields = array(
            'registration' => strtoupper(urlencode($plate)),
            'origin' => strtoupper(urlencode($state)),
            'vrn' => ''
        );

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        $dom = str_get_html($result);

        $form_search_result = $dom->find('#form-search-result', 0);
        if ($form_search_result) {
            $q = trim($form_search_result->last_child()->value);
        } else if (strpos($result,"<h2>Website Error</h2>")) {
            // Alert user of M5 outage
            $this->session->set_flashdata('m5_system_unavailable', 'true');
            return false;
        } else {
            mail("debug@mytolls.com","M5 Debug",$result);
            return false;
        }

        $url = "https://webpayments.m5motorway.com.au/payment/init";

        $fields = array(
            'registration%5B%5D' => str_pad(urlencode($plate),10,"+"),
            'method' => 'CC',
            'q' => $q
        );
        //url-ify the data for the POST
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

        //execute post
        $result = curl_exec($ch);

        $dom = str_get_html($result);

        if ($dom==null) {
            $this->session->set_flashdata('m5_system_unavailable', 'true');
            mail('<SNIPPED>', 'M5 Debug', $result);
            return false;
        }

        $table = $dom->find('.m5-table', 0);
        if ($table==null)
            return false;
        $tr = $table->find('tr', 1);
        if ($tr==null)
            return false;
        $num_trips = $this->toNumber(trim($tr->find('td', 1)->plaintext));
        $toll_charge = $this->toNumber($table->find('tr', 1)->find('td', 2)->{'data-tollfare'});
        $raw_toll_charge = $table->find('tr', 1)->find('td', 2)->{'data-tollfare'};
        $admin_charge = $this->toNumber(trim($table->find('tr', 2)->find('td', 2)->plaintext));
        $raw_admin_charge = $this->toNumber(str_replace("$","",trim($table->find('tr', 2)->find('td', 2)->plaintext)));

        /* MUST back out and go down tag route to get current rates
        $fields = array(
            'registration%5B%5D' => str_pad(urlencode($plate),10,"+"),
            'method' => 'MD',
            'q' => $q
        );
        //url-ify the data for the POST
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

        //execute post
        curl_exec($ch);

        // TODO: Check for excess admin fees for M5

        curl_setopt($ch,CURLOPT_URL, "https://webpayments.m5motorway.com.au/payment/get_adminfees?issuer=102&enabled=1&q=".$q);
        curl_setopt($ch,CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_HTTPGET, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, null);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array());

        //execute get
        $result = curl_exec($ch);

        $split = explode('"',str_replace('{"adminfees":{"','',$result));

        $admin_charge_tag_rate = $split[0];
        */

        $admin_charge_tag_rate = 2.2; // TODO: Fix this

        $discounted_admin_charge = $this->toNumber($admin_charge_tag_rate) * $num_trips;
        if (!is_numeric($discounted_admin_charge))
            $discounted_admin_charge = $admin_charge;
        else {
            $discounted_admin_charge = $this->toNumber($admin_charge_tag_rate) * $num_trips;
            $raw_discounted_admin_charge = "$" . number_format($discounted_admin_charge, 2);
        }

        if ($discounted_admin_charge<0.1 || $discounted_admin_charge=="Unknown") {
            $admin_charge = 0.1;
            $discounted_admin_charge = 0.1;
            $raw_discounted_admin_charge = 0.1;
        }

        //close connection
        curl_close($ch);

        $total_price = $this->toNumber($raw_toll_charge)+$this->toNumber($raw_admin_charge);
        $discounted_total_price = $this->toNumber($raw_toll_charge)+$this->toNumber($raw_discounted_admin_charge);

        $result_entry = array(
            'status' => 'Unpaid',
            'system_data' => '',
            'discounted_admin_charge' => $discounted_admin_charge,
            'rego' => $plate,
            'notice_text' => "",
            'admin_charge' => $admin_charge,
            'toll_charge' => $toll_charge,
            'details_raw' => null,
            'time' => "<i>Unknown</i>",
            'datetime' => "Unknown",
            'timestamp' => null,
            'discounted_total_price' => $discounted_total_price,
            'total_price' => $total_price,
            'num_trips' => $num_trips,
            'motorway' => "M5 South-West Motorway",
            'toll_provider' => 'M5'
        );

        return $result_entry;
    }

    public function processM5Tolls($plate, $state, $payment_total) {
        if ($state==1) { $state = "ACT"; }
        if ($state==2) { $state = "NSW"; }
        if ($state==3) { $state = "VIC"; }
        if ($state==4) { $state = "QLD"; }
        if ($state==5) { $state = "SA"; }
        if ($state==6) { $state = "WA"; }
        if ($state==7) { $state = "TAS"; }
        if ($state==8) { $state = "NT"; }

        try {

        $cookiefile = '<SNIPPED>/cookies_'.uniqid().'.txt';
        file_put_contents($cookiefile, '');

        //extract data from the post
        //set POST variables
        $url = 'https://webpayments.m5motorway.com.au/search/do';

        //open connection
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $fields = array(
            'registration' => strtoupper(urlencode($plate)),
            'origin' => strtoupper(urlencode($state)),
            'vrn' => ''
        );

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        $dom = str_get_html($result);

        $form_search_result = $dom->find('#form-search-result', 0);
        if ($form_search_result) {
            $q = trim($form_search_result->last_child()->value);
        } else {
            mail("debug@mytolls.com","M5 Debug DURING AUTOPAY",$result);
            // TODO: Alert user to system partial outage
            return 0;
        }

        $url = "https://webpayments.m5motorway.com.au/payment/init";

        $fields = array(
            'registration%5B%5D' => str_pad(urlencode($plate),10,"+"),
            'method' => 'MD',
            'q' => $q
        );
        //url-ify the data for the POST
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

        //execute post
        $result = curl_exec($ch);

        $url = 'https://webpayments.m5motorway.com.au/payment/get_adminfees?issuer=102&q='.$q.'&enabled=1';

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, false);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array());

        $result = curl_exec($ch);

        //

        $url = 'https://webpayments.m5motorway.com.au/payment/prepare';

        $fields = array(
            'issuer' => '102',
            'customer_name' => 'myTolls+Admin',
            'account_number' => '<SNIPPED>',
            'tag_number' => '<SNIPPED>',
            'contact_number' => '0408509406',
            'contact_email' => 'admin%40mytolls.com',
            'confirm_contact_email' => 'admin%40mytolls.com',
            'method' => 'MD',
            'q' => $q
        );
        //url-ify the data for the POST
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

        //execute post
        $result = curl_exec($ch);

        $dom = str_get_html($result);

        $table = $dom->find('table', 1);
        if ($table==null) {
            return 0;
        }
        $table = $table->children(0); //find('tbody', 0);

        if ($table==null)
            return 0;
        $tr = $table->find('tr', 4);
        if ($tr==null)
            return 0;

        $total_price = $this->toNumber($tr->find('td', 1)->plaintext);

        /*
        $num_trips = $this->toNumber(trim($tr->find('td', 1)->plaintext));
        $toll_charge = $this->toNumber($table->find('tr', 1)->find('td', 2)->plaintext);
        $raw_toll_charge = $table->find('tr', 1)->find('td', 2)->plaintext;
        $admin_charge = $this->toNumber(trim($table->find('tr', 2)->find('td', 2)->plaintext));
        $raw_admin_charge = $this->toNumber(str_replace("$","",trim($table->find('tr', 2)->find('td', 2)->plaintext)));

        $admin_charge_tag_rate = 2.2;

        $total_price = $toll_charge + ($num_trips * $admin_charge_tag_rate);
        */

        if ($total_price>$this->toNumber($payment_total)) {
            mail('<SNIPPED>', 'Toll Payment for '.$plate.' ERROR (Too High Tolls M5)',$total_price." > ".$this->toNumber($payment_total));
            exit;
        }

        $url = 'https://webpayments.m5motorway.com.au/payment/do';

        $fields = array(
            'q' => $q
        );
        //url-ify the data for the POST
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        mail("debug@mytolls.com","M5 Debug total_price",$total_price);

        } catch (Exception $e) {
            mail('<SNIPPED>', 'ERROR: Exception paying M5 for '.$plate,$e->getMessage());
            mail('<SNIPPED>', 'ERROR: Exception paying M5 for '.$plate,$e->getTraceAsString());
            return 0;
        }

        return $total_price;
    }

    public function getRegoDetails($plate, $state = 'nsw') {
        $cookiefile = '<SNIPPED>/cookies_'.uniqid().'.txt';
        file_put_contents($cookiefile, '');

        if ($state=="nsw") {
            $url = "https://www.myrta.com/wps/portal/extvp/myrta/rego/check-reg-history/!ut/p/b1/pZHJjqNADEC_hQ8YVVUgLMdiSYAJS9ipS0SAZmmWQDMk8PVNWppjJofxybae5WcZEBAjmtnT_J5nBRAB0iVzVSRT1XdJ86wJe4GqYgf-jkPQ1VioiX5gBZhGR5rZgPgJvAgM382HIILMxa35m7FM0WmVZq9ezztTjlejzu-TfEamebKzwPFFLF6TMRj-e6cOSFrm6aeTF9XXNP7cqm5ZPy6bD_nnuMq9AVj6L_Daz1T7NgfxhnGvPffAA9EhLTi5x4WCI3zklIdZchjdYsXqtTlN6UVK1t985t_Kdd0bwqNsD0MiTa7itWw1rk3ApBXbOEjypj-HebWO4ReR5aZzTpi1itJPQnloiM_MkxPvpjtFvZPinlI_D6M1Q7JllNGKyUfZkn0ohjyLj1p1EIwFKNE6qs_C1s_t7ELu2cc1FJfekDvFPB-soG1yNyzdsL0dSafbw2czuK3eeKMujH7CaFi_BnULKdCS5rT8ch31jniIKeobzZr8Fg!!/dl4/d5/L0lDU0lKSmdwcGlRb0tVUm1ZQSEhL29Pb2dBRUlRaGpFQ1VJZ0FJQUl5RkFNaHdVaFM0SlJFQUlCR2lJQVFFREVRQWdBQS80RzNhRDJnanZ5aERVd3BNaFQ5VUlnISEvWjdfMEhFUFZVMjcxMFNJNjBJQlVWT1ZBMzFHMzUvMC8zMTM2MjcxMDMwMDgvc3VibWl0RW5xdWlyeQ!!";

            //open connection
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $fields = array(
                'enquiryRequest.plateNumber' => urlencode($plate)
            );

            //url-ify the data for the POST
            $fields_string = http_build_query($fields);

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);

            //close connection
            curl_close($ch);

            $dom = str_get_html($result);

            $rms_content = $dom->find(".rms_content", 0);
            if ($rms_content == null)
                return array("No vehicle details found with that registration", "");
            $rms_content_inner = $rms_content->find("p", 0);

            $rms_content_parts = explode("<br/>", $rms_content_inner->innertext);
            $vin = trim(array_pop($rms_content_parts));

            return array(trim(implode(" ", $rms_content_parts)), $vin, false);
        } else {
            return array("Registration details for ".strtoupper($state)." are not currently available.","",false);
        }
    }
}