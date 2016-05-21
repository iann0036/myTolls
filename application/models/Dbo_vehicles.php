<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dbo_vehicles extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get($plate, $state) {
        $this->db->where('plate',$plate);
        $this->db->where('state',$state);
        $results = $this->db->get('vehicles');

        if ($results->num_rows()<1) {
            return false;
        }

        $row = $results->row();

        $this->session->set_userdata(array('vehicle_id' => $row->id));

        $isProcessing = false;
        if ($row->processingstart!=null) {
            $processingStartTime = strtotime($row->processingstart);
            $expiryTime = strtotime("-3 days");

            if ($processingStartTime>$expiryTime)
                $isProcessing = true;
        }

        return array($row->description, $row->vin, $isProcessing);
    }

    public function put($plate, $state, $rego_details, $isbike = false) {
        $insert_data = array(
            'plate' => $plate,
            'state' => $state,
            'description' => $rego_details[0],
            'vin' => $rego_details[1],
            'isbike' => $isbike
        );
        $result = $this->db->insert('vehicles', $insert_data);

        $this->session->set_userdata(array('vehicle_id' => $this->db->insert_id()));
    }

    public function setProcessingDate($plate, $state) {
        $this->db->where('plate',$plate);
        $this->db->where('state',$state);

        $update_data = array(
            'processingstart' => date('Y-m-d H:i:s')
        );
        $this->db->update('vehicles', $update_data);
    }
}