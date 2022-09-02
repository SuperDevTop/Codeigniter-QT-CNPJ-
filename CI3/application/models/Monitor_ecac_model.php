<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Monitor_ecac_model extends CI_Model {

	public function listar() {
		$this->db->select("*");
		$this->db->from('dtb_empresas_sem_atualizar');
		$this->db->group_by('dtb_empresas_sem_atualizar.banco_de_dados');		
		return $this->db->get()->result();
	}

	public function quantidade_de_empresas_dg(){

		$this->db->select('COUNT(distinct(dtb_empresas_sem_atualizar.cnpj)) AS valor');		
		// $this->db->group_by('dtb_empresas_sem_atualizar.banco_de_dados');
		return $this->db->get('dtb_empresas_sem_atualizar')->row();
	}

}