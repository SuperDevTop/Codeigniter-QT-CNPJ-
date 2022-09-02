<?php

class Procuracao_model extends CI_Model {

    public $cnpj;
    public $cnpj_outorgante;
    public $nome_outorgante;
    public $data_inicio;
    public $data_fim;
    public $situacao;


    public function clear($cnpj, $banco){
        return $this->db->truncate($banco.'.dtb_ecac_procuracao');
    }

	public function insert($cnpj, $procuracao, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$this->cnpj = $cnpj;
        $this->cnpj_outorgante = preg_replace('/[^0-9]/', '', $procuracao['cnpj_outorgante']);
        $this->nome_outorgante = $procuracao['nome_outorgante'];
        $this->data_inicio = $procuracao['data_inicio'];
        $this->data_fim = $procuracao['data_fim'];
        $this->situacao = $procuracao['situacao'];

		$this->db->insert($banco.'.dtb_ecac_procuracao', $this);
		return $this->db->insert_id();
	}
}
