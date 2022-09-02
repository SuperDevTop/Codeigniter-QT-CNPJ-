<?php

class Certificado_teste_model extends CI_Model {

	public function get($banco)
	{		

		$this->db->join($banco.'.dtb_situacao_fiscal as e', 'dtb_certificado.cnpj_data = e.cnpj_data', 'left');
		$this->db->where('DATE(e.data_execucao) < CURRENT_DATE()');
		return $this->db->get($banco.'.dtb_certificado')->result(); 
	}

	// public function get($banco)
	// {		

	// 	$this->db->join($banco.'.dtb_situacao_fiscal as e', 'dtb_certificado.cnpj_data = e.cnpj_data', 'left');
	// 	$this->db->where('e.cnpj_data','29271374000123');
	// 	return $this->db->get($banco.'.dtb_certificado')->result(); 
	// }
}
