<?php

class Situacao_cadin_model extends CI_Model {

	public $possui_pendencia;
	public $data_pdf;
	public $data_execucao;
	public $cnpj_data;

	public function verifica_se_existe($cnpj_data, $banco){
		$this->db->select('COUNT(distinct(dtb_situacao_cadin.id)) AS qtd');
		$this->db->where('cnpj_data', $cnpj_data);
		return $this->db->get($banco.'.dtb_situacao_cadin')->row();
	}

	public function insert($possui_pendencia, $data_pdf, $caminho_download, $cnpj_data, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$this->possui_pendencia = $possui_pendencia;
		$this->data_pdf = null;
		$this->data_execucao = date('Y-m-d H:i:s');
		$this->cnpj_data = $cnpj_data;
		$this->caminho_download = $caminho_download;

		$this->db->insert($banco.'.dtb_situacao_cadin', $this);
		return $this->db->insert_id();
	}

	public function update($possui_pendencia, $data_pdf, $caminho_download, $cnpj_data, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$dados = array(	

				'possui_pendencia' => $possui_pendencia,
				'data_pdf' => null,
				'caminho_download' => $caminho_download,
				'data_execucao' => date('Y-m-d H:i:s'),
				'cnpj_data' => $cnpj_data
		);
	
		if ($this->db->update($banco.'.dtb_situacao_cadin', $dados, "cnpj_data=".$cnpj_data)){
			return TRUE;
		} else {
			return FALSE;
		}

	}
}
