<?php

class Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model extends CI_Model {

    public $id;
    public $id_tributo;
    public $cnpj;
    public $numero_parcela;
    public $data_vencimento;
    public $valor_ate_vencimento;
    public $saldo_devedor_atual;
    public $situacao;
    public $path_download_parcela;

    public function clear($id_tributo, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_nao_previdenciario_demonstrativo_das_parcelas', "id_tributo = '{$id_tributo}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->id_tributo = $dados['id_tributo'];
        $this->cnpj = $dados['cnpj'];
        $this->numero_parcela = $dados['numero_parcela'];
        $this->data_vencimento = $dados['data_vencimento'];
        $this->valor_ate_vencimento = $dados['valor_ate_vencimento'];
        $this->saldo_devedor_atual = $dados['saldo_devedor_atual'];
        $this->situacao = $dados['situacao'];

        $this->db->insert($banco.'.dtb_parcelamento_nao_previdenciario_demonstrativo_das_parcelas', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('saldo_devedor_atual', $dados['saldo_devedor_atual']);
        $this->db->set('situacao', $dados['situacao']);
        $this->db->set('data_vencimento', $dados['data_vencimento']);
        $this->db->set('valor_ate_vencimento', $dados['valor_ate_vencimento']);
        $this->db->set('saldo_devedor_atual', $dados['saldo_devedor_atual']);

        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('id_tributo', $dados['id_tributo']);
        $this->db->where('numero_parcela', $dados['numero_parcela']);
        $this->db->update($banco.'.dtb_parcelamento_nao_previdenciario_demonstrativo_das_parcelas');
    }

    public function verifica_se_existe($cnpj, $banco, $id_tributo, $numero_parcela){
        $this->db->select('COUNT(*) as qtd, path_download_parcela, situacao');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('id_tributo', $id_tributo);
        $this->db->where('numero_parcela', $numero_parcela);
        return $this->db->get($banco.'.dtb_parcelamento_nao_previdenciario_demonstrativo_das_parcelas')->row();
    }

    public function update_path($banco, $numero_parcela, $cnpj, $id_tributo, $caminho_download){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('path_download_parcela', $caminho_download);

        $this->db->where('cnpj', $cnpj);
        $this->db->where('id_tributo', $id_tributo);
        $this->db->where('numero_parcela', $numero_parcela);

        $this->db->update($banco.'.dtb_parcelamento_nao_previdenciario_demonstrativo_das_parcelas');
    }
}
