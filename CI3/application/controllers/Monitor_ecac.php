<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitor_ecac extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('monitor_ecac_model');
	}

	public function index()
	{	

		$dados['monitor'] = $this->monitor_ecac_model->listar();
		$dados['qtd'] = $this->monitor_ecac_model->quantidade_de_empresas_dg();

		$this->load->view('layout/head');
		$this->load->view('layout/header');		
		$this->load->view('layout/sidebar');		
		$this->load->view('monitor_ecac/listar', $dados); 
		$this->load->view('layout/footer'); 
	}
}