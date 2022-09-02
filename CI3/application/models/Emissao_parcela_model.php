<?php

class Emissao_parcela_model extends CI_Model {

	public $valor;
	public $data_parcela;
	public $data_execucao;
	public $cnpj_data;

	public function insert($data,  $cnpj_data, $banco)
	{
		$this->valor = $data['valor'];
		$this->data_execucao     = date('Y-m-d H:i:s');
		$this->data_parcela	 = $data['data_parcela'];
		$this->cnpj_data = $cnpj_data;

		$this->db->insert($banco.'.dtb_parcelas_emitidas', $this);
	}

	public function delete_parcelas($cnpj_data, $banco){
		return $this->db->delete($banco.'.dtb_parcelas_emitidas', "cnpj_data = {$cnpj_data}");
	}
}
