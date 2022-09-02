<?php

class Darf_model extends CI_Model {

    public $cnpj;
    public $tipo_documento;
    public $numero_documento;
    public $periodo_apuracao;
    public $periodo_arrecadacao;
    public $data_vencimento;
    public $codigo_receita;
    public $numero_referencia;
    public $valor_total;
    public $path_download;
    public $data_execucao;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_darf', "cnpj = {$cnpj}");
    }

	public function insert($darf, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$this->cnpj = $darf['cnpj'];
        $this->tipo_documento = $darf['tipo_documento'];
        $this->numero_documento = $darf['numero_documento'];
        $this->periodo_apuracao = $darf['periodo_apuracao'];
        $this->periodo_arrecadacao = $darf['periodo_arrecadacao'];
        $this->data_vencimento = $darf['data_vencimento'];
        $this->codigo_receita = $darf['codigo_receita'];
        $this->numero_referencia = $darf['numero_referencia'];
        $this->valor_total = $darf['valor_total'];
        $this->path_download = $darf['path_download'];
        $this->data_execucao = date('Y-m-d H:i:s');

		$this->db->insert($banco.'.dtb_ecac_darf', $this);
		return $this->db->insert_id();
	}

    public function find_all_darfs($banco)
    {       
        $this->db->select('*');
        return $this->db->get($banco.'.dtb_ecac_darf')->result();
    }
}
