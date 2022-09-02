<?php

class Cnd_itagimirim_model extends CI_Model {

	public $status;
	public $cnpj;
    public $cnpj_completo;
    public $inscricao_estadual;
    public $inscricao_estadual_completo;
    public $razao_social;

    public function verifica_se_existe($cnpj, $banco){
		$this->db->select('COUNT(distinct(dtb_certidao_itagimirim.cnpj)) AS qtd');
		$this->db->where('cnpj', $cnpj);
		return $this->db->get($banco.'.dtb_certidao_itagimirim')->row();
	}

	public function insert($status, $caminho_download, $cnpj, $cnpj_completo, $inscricao_estadual, $inscricao_estadual_completo, $razao_social, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$this->status = $status;
		$this->cnpj = $cnpj;
        $this->cnpj_completo = $cnpj_completo;
        $this->inscricao_estadual = $inscricao_estadual;
        $this->inscricao_estadual_completo = $inscricao_estadual_completo;
        $this->razao_social = $razao_social;
		$this->caminho_download = $caminho_download;

		$this->db->insert($banco.'.dtb_certidao_itagimirim', $this);
		return $this->db->insert_id();
	}

	public function update($status, $caminho_download, $cnpj, $cnpj_completo, $inscricao_estadual, $inscricao_estadual_completo, $razao_social, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$dados = array(	
            'status' => $status,
            'caminho_download' => $caminho_download,
            'cnpj' => $cnpj,
            'cnpj_completo' => $cnpj_completo,
            'inscricao_estadual' => $inscricao_estadual,
            'inscricao_estadual_completo' => $inscricao_estadual_completo,
            'razao_social' => $razao_social,
        );
	
		if ($this->db->update($banco.'.dtb_certidao_itagimirim', $dados, "cnpj=".$cnpj)){
			return TRUE;
		} else {
			return FALSE;
		}

	}
}
