<?php

class Relp_emissao_parcela_model extends CI_Model {

	public $valor;
	public $data_parcela;
	public $cnpj;
    public $path_download_parcela;

    public function get_parcela_sem_emitir($banco, $cnpj){
        $this->db->select('*');
        $this->db->like('data_parcela', date('m/Y'), 'both');
        $this->db->where('path_download_parcela is null');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_relp_parcelas_emitidas')->row();
    }

    public function verifica_se_existe_parcela_pra_emitir($banco){
        $this->db->select('count(*) as qtd');
        $this->db->like('data_parcela', date('m/Y'), 'both');
        $this->db->where('path_download_parcela is null');
        return $this->db->get($banco.'.dtb_relp_parcelas_emitidas')->row();
    }

	public function insert($cnpj, $banco, $dados)
	{
		$this->valor = $dados['valor'];
		$this->data_parcela	 = $dados['data_parcela'];
		$this->cnpj = $cnpj;
		$this->db->insert($banco.'.dtb_relp_parcelas_emitidas', $this);
	}

    public function update($cnpj, $banco, $dados)
    {
        $this->db->set('valor', $dados['valor']);
        $this->db->where('data_parcela', $dados['data_parcela']);
        $this->db->where('cnpj', $cnpj);
        $this->db->update($banco.'.dtb_relp_parcelas_emitidas');
    }

    public function verifica_se_existe($banco, $cnpj, $data_parcela ){
        $this->db->select('COUNT(distinct(dtb_relp_parcelas_emitidas.data_parcela)) AS qtd, path_download_parcela');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('data_parcela', $data_parcela);
        return $this->db->get($banco.'.dtb_relp_parcelas_emitidas')->row();
    }

    public function update_path($banco, $data_parcela, $cnpj, $path_download_parcela)
    {
        $this->db->set('path_download_parcela', $path_download_parcela);
        $this->db->where('data_parcela', $data_parcela);
        $this->db->where('cnpj', $cnpj);
        $this->db->update($banco.'.dtb_relp_parcelas_emitidas');
    }
}
