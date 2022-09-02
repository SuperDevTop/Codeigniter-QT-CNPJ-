<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Plano_model extends CI_Model {

	public function valor_max_empresas() {
		$this->db->select('qtd_empresas as valor');
		$this->db->from('dtb_plano_contratado');
		$this->db->where('id', 1);
		return $this->db->get()->row();
	}

	public function valor_max_usuarios() {
		$this->db->select('qtd_usuarios as valor');
		$this->db->from('dtb_plano_contratado');
		$this->db->where('id', 1);
		return $this->db->get()->row();
	}

	public function valor_caminho() {
		$this->db->select('caminho_arq as valor');
		$this->db->from('dtb_certificado');
		$this->db->where('id_empresa', 483);
		return $this->db->get()->row();
	}


	

	public function nome_do_plano() {
		$this->db->select('nome_do_plano as valor');
		$this->db->from('dtb_plano_contratado');
		$this->db->where('id', 1);
		return $this->db->get()->row();
	}

	public function pesquisar_plano_id(){
		$this->db->select('*');
		$this->db->where('id', 1);
		return $this->db->get('dtb_plano_contratado')->row();
	}

	public function editar_plano($nome_plano,$qtd_empresas,$qtd_usuarios){
	
		$dados = array(	

				'nome_do_plano' => $nome_plano,
				'qtd_empresas' => $qtd_empresas,
				'qtd_usuarios' => $qtd_usuarios
		);
	
		if ($this->db->update('dtb_plano_contratado', $dados, "id=1")){
			return TRUE;
		} else {
			return FALSE;
		}	
	}

	public function listar(){
		
		$this->db->where('id', 1);
		return $this->db->get('dtb_plano_contratado')->result();
	}


}