<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Teste extends CI_Controller {

	public function index()
	{
		$this->load->view('layout/head');
		$this->load->view('layout/header');	 
		$this->load->view('pagina_teste/teste'); 
	}


	public function solicitar(){ 
		// echo "aaaaa";
		// sleep(15);

		for ($i = 0; $i < 1000000000; $i++) { 
			
		}

		// $resposta = $this->input->post("cnpj");

		$count =  0;
		foreach($this->input->post() as $key => $value)
		{
			$length = strlen($key);

			if($count == 0)
			{
				echo substr($key, 15, $length - 19);
			}

			$count++;
		}		
		// $resposta = "79789";
		// echo "SEU CNPJ É: ".$resposta; 

		// foreach($this->input->post(NULL, TRUE) as $key => $val)
		// {
		// 	echo "aa";
		// 	echo substr($key, 15, 19);
		// }
			
		// $data = json_encode(array(
		// 	'returned '=> $resposta 
		// ));
			
	}
}