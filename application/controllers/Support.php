<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Support extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }

	public function index() {
        $mailsent = false;
        if ($this->input->get_post('message')) {
            mail('<SNIPPED>','myTolls Support Contact Form','First Name: '.$this->input->get_post('fname').
                ', Last Name: '.$this->input->get_post('lname').', E-mail: '.$this->input->get_post('email').', Message: '.
                $this->input->get_post('message'));
            $mailsent = true;
        }

        $this->load->view('header');
        $this->load->view('support',array(
            'mailsent' => $mailsent
        ));
        $this->load->view('footer');
	}
}
