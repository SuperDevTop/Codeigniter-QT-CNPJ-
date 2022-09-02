<?php

class Parcelamento_pert_rfb_demonstrativo_de_parcelas_model extends CI_Model {
    public $id;
    public $id_parcelamento;
    public $cnpj;
    public $data_vencimento;
    public $id_parcela;
    public $numero_parcela;
    public $saldo_atualizado;
    public $situacao;
    public $valor_originario;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $dados->cnpj;
        $this->id_parcelamento = $dados->id_parcelamento;
        $this->data_vencimento = $dados->dataVencimento;
        $this->id_parcela = $dados->id;
        $this->numero_parcela = $dados->numeroParcela;
        $this->saldo_atualizado = $dados->saldoAtualizado;
        $this->situacao = $dados->situacao;
        $this->valor_originario = $dados->valorOriginario;

        $this->db->insert($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('saldo_atualizado', $dados->saldoAtualizado);
        $this->db->set('situacao', $dados->situacao);

        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('id_parcela', $dados->id);

        $this->db->update($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas');
    }

    public function verifica_se_existe($dados, $banco){
        $this->db->select('COUNT(distinct(dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas.id)) AS qtd, dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas.id AS id, dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas.path_download_parcela AS path_download_parcela');
       
        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('id_parcela', $dados->id);
        
        return $this->db->get($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas')->row();
    }

    public function update_path($dados, $banco, $path_download_parcela){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('path_download_parcela', $path_download_parcela);

        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('id_parcela', $dados->id);

        $this->db->update($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_parcelas');
    }
}
