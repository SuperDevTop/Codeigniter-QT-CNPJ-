<?php

class Caixa_postal_mensagem_model extends CI_Model {

	public $assunto;
	public $remetente;
	public $recebida_em;
	public $caixa_postal_id;
	public $lida;
	public $importante;
	public $id_mensagem;
	public $codigo_receita_ecac;

	public function insert($data, $banco)
	{
		$var = $data['recebida_em'];
		$date = str_replace('/', '-', $var);

		$this->assunto    = $data['assunto'];
		$this->conteudo = $data['conteudo'];
		$this->remetente  = $data['remetente'];
		$this->recebida_em     = date('Y-m-d', strtotime($date));
		$this->caixa_postal_id     = $data['caixa_postal_id'];
		$this->lida = $data['lida'];
		$this->importante = $data['importante'];
		$this->id_mensagem = $data['id_mensagem'];
		$this->codigo_receita_ecac = $data['codigo_receita_ecac'];

		$this->db->insert($banco.'.dtb_ecac_caixa_postal_mensagem', $this);
		return $this->db->insert_id();
	}

	public function limpaTabelaMensagens($caixa_postal_id, $banco){
		return $this->db->delete($banco.'.dtb_ecac_caixa_postal_mensagem', "caixa_postal_id = {$caixa_postal_id}");
	}

	public function busca_mensagens_ja_lidas($cnpj, $banco){
		$this->db->select('*');
		$this->db->join($banco.'.dtb_ecac_caixa_postal c','a.caixa_postal_id = c.id');
		$this->db->where('a.lida = 1');
		$this->db->where('c.cnpj_data', $cnpj);
		return $this->db->get($banco.'.dtb_ecac_caixa_postal_mensagem a')->row();
	}

	public function update_conteudo($id, $conteudo,$banco){
		$campos = array(
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

	public function busca_mensagens_existentes($banco, $mensagem, $caixa_postal_id){
		$var = $mensagem['recebida_em'];
		$date = str_replace('/', '-', $var);

		$this->db->select('COUNT(distinct(a.id)) AS qtd');
		$this->db->where('assunto', $mensagem['assunto']);
		$this->db->where('remetente', $mensagem['remetente']);
		$this->db->where('recebida_em', date('Y-m-d', strtotime($date)));
		$this->db->where('caixa_postal_id', $caixa_postal_id);
		return $this->db->get($banco.'.dtb_ecac_caixa_postal_mensagem a')->row();
	}

}
