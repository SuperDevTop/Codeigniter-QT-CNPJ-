<?php

class Dctf_web_model extends CI_Model
{
    public $cnpj;
    public $id_declaracao;
    public $id_controle;
    public $periodo_apuracao;
    public $data_transmissao;
    public $categoria;
    public $origem;
    public $tipo;
    public $situacao;
    public $debito_apurado;
    public $saldo_a_pagar;
    public $status;
    public $path_download_recibo;
    public $path_download_extrato;
    public $path_download_darf;

    public function insert($dados, $banco)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $dados['cnpj'];
        $this->id_declaracao = $dados['id_declaracao'];
        $this->id_controle = $dados['id_controle'];
        $this->periodo_apuracao = $dados['periodo_apuracao'];
        $this->data_transmissao = $dados['data_transmissao'];
        $this->categoria = $dados['categoria'];
        $this->origem = $dados['origem'];
        $this->tipo = $dados['tipo'];
        $this->situacao = $dados['situacao'];
        $this->debito_apurado = $dados['debito_apurado'];
        $this->saldo_a_pagar = $dados['saldo_a_pagar'];
        $this->status = $dados['status'];

        $this->db->insert($banco . '.dtb_ecac_dctf_web', $this);
        return $this->db->insert_id();
    }

    public function verifica_se_existe($dados, $banco)
    {
        $this->db->select('COUNT(distinct(db.id)) AS qtd, db.id as id, db.path_download_darf AS path_download_darf,	db.path_download_recibo AS path_download_recibo, db.path_download_extrato AS path_download_extrato');
        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('periodo_apuracao', $dados['periodo_apuracao']);
        $this->db->where('tipo', $dados['tipo']);
        $this->db->where('saldo_a_pagar', $dados['saldo_a_pagar']);

        return $this->db->get($banco . '.dtb_ecac_dctf_web as db')->row();
    }

    public function update($dados, $id, $banco)
    {
        $this->db->set('periodo_apuracao', $dados['periodo_apuracao']);
        $this->db->set('data_transmissao', $dados['data_transmissao']);
        $this->db->set('situacao', $dados['situacao']);
        $this->db->set('debito_apurado',  $dados['debito_apurado']);
        $this->db->set('saldo_a_pagar', $dados['saldo_a_pagar']);
        $this->db->set('status', $dados['status']);

        $this->db->where('id', $id);
        $this->db->update($banco . '.dtb_ecac_dctf_web');
    }
    
    public function update_path_darf($id, $caminho_download, $banco)
    {
        $this->db->set('path_download_darf', $caminho_download);
        $this->db->where('id', $id);
        $this->db->update($banco . '.dtb_ecac_dctf_web');
    }

    public function update_path_recibo($id, $caminho_download, $banco)
    {
        $this->db->set('path_download_recibo', $caminho_download);
        $this->db->where('id', $id);
        $this->db->update($banco . '.dtb_ecac_dctf_web');
    }

    public function update_path_extrato($id, $caminho_download, $banco)
    {
        $this->db->set('path_download_extrato', $caminho_download);
        $this->db->where('id', $id);
        $this->db->update($banco . '.dtb_ecac_dctf_web');
    }
}
