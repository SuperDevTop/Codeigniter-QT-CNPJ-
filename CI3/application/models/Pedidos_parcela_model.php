<?php

class Pedidos_parcela_model extends CI_Model {

	public $possui_pedido;
	public $data_execucao;
	public $cnpj_data;

	public function insert($possui_pedido,  $cnpj_data, $banco)
	{
		$this->possui_pedido = $possui_pedido;
		$this->data_execucao     = date('Y-m-d H:i:s');
		$this->cnpj_data = $cnpj_data;

		$this->db->insert($banco.'.dtb_consulta_pedidos', $this);
	}

	public function delete_consulta_pedido($cnpj_data, $banco){
		return $this->db->delete($banco.'.dtb_consulta_pedidos', "cnpj_data = {$cnpj_data}");
	}
}
