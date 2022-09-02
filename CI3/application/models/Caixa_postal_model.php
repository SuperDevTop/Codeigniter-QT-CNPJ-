<?php

class Caixa_postal_model extends CI_Model {

	public $lidas;
	public $nao_lidas;
	public $data_execucao;
	public $cnpj_data;


	public function insert($data, $cnpj, $banco)
	{
		date_default_timezone_set('America/Sao_Paulo');
		$this->lidas = $data['lidas'];
		$this->nao_lidas = $data['nao_lidas'];
		$this->data_execucao = date('Y-m-d H:i:s');
		$this->cnpj_data  = $cnpj;

		$this->db->insert($banco.'.dtb_ecac_caixa_postal', $this);
		return $this->db->insert_id();
	}

	public function existe_caixa_postal($cnpj_data, $banco){
		$this->db->select('COUNT(distinct(dtb_ecac_caixa_postal.id)) AS qtd, dtb_ecac_caixa_postal.id as id');
		$this->db->where('cnpj_data', $cnpj_data);
		return $this->db->get($banco.'.dtb_ecac_caixa_postal')->row();
	}

	public function update($data, $cnpj, $id, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$dados = array(	

				'lidas' => $data['lidas'],
				'nao_lidas' => $data['nao_lidas'],
				'data_execucao' => date('Y-m-d H:i:s'),
				'cnpj_data' => $cnpj
		);
	
		if ($this->db->update($banco.'.dtb_ecac_caixa_postal', $dados, "id=".$id)){
			return TRUE;
		} else {
			return FALSE;
		}

	}
}
