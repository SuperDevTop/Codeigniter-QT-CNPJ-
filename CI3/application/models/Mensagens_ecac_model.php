<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mensagens_ecac_model extends CI_Model {

	public function find_mensagem($id,$banco){
		$this->db->select('id_mensagem');
		$this->db->where('dtb_ecac_caixa_postal_mensagem.id', $id);
		return $this->db->get($banco.'.dtb_ecac_caixa_postal_mensagem')->row();
	}

	public function find_cnpj($id_caixa_postal,$banco){
		$this->db->select('cnpj_data');
		$this->db->where('dtb_ecac_caixa_postal.id', $id_caixa_postal);
		return $this->db->get($banco.'.dtb_ecac_caixa_postal')->row();
	}

	public function find_certificado($cnpj,$banco){
		$this->db->select('*');
		$this->db->where('cnpj_data', $cnpj);
		return $this->db->get($banco.'.dtb_certificado')->row();
	}

	public function update_lida($id, $conteudo,$banco){
		$campos = array(
			'lida' => 1,
			'conteudo' => $conteudo
		);

		$this->db->where('id',$id);
		$this->db->update($banco.'.dtb_ecac_caixa_postal_mensagem',$campos);

		if ($this->db->affected_rows() == 1) {
			return 1;
		}else{
			return 0;
		}
	}

	public function update_lida_caixa_postal($id,$banco){
		$sql = 'UPDATE `'.$banco.'`.dtb_ecac_caixa_postal set nao_lidas = (nao_lidas - 1) where id = '.$id;
		$this->db->query($sql);
	}

	public function find_empresa_by_cnpj($cnpj,$banco){
		$this->db->select('*');
		$this->db->where("cnpj", $cnpj);

		return $this->db->get($banco.'.dtb_empresas')->row();
	}
}