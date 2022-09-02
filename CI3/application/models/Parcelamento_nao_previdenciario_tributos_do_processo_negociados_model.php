<?php

class Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model extends CI_Model {

    public $id;
    public $id_processo;
    public $cnpj;
    public $tributo;
    public $situacao;
    public $saldo;
    public $total_em_atraso;
    public $parcelas_em_atraso;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_nao_previdenciario_tributos_processo_negociados', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->id_processo = $dados['id_processo'];
        $this->cnpj = $dados['cnpj'];
        $this->tributo = $dados['tributo'];
        $this->situacao = $dados['situacao'];
        $this->saldo = $dados['saldo'];
        $this->total_em_atraso = $dados['total_em_atraso'];
        $this->parcelas_em_atraso = $dados['parcelas_em_atraso'];

        $this->db->insert($banco.'.dtb_parcelamento_nao_previdenciario_tributos_processo_negociados', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('situacao', $dados['situacao']);
        $this->db->set('saldo', $dados['saldo']);
        $this->db->set('total_em_atraso', $dados['total_em_atraso']);
        $this->db->set('situacao', $dados['situacao']);

        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('id_processo', $dados['id_processo']);
        $this->db->where('tributo', $dados['tributo']);

        $this->db->update($banco.'.dtb_parcelamento_nao_previdenciario_tributos_processo_negociados');

        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('id_processo', $dados['id_processo']);
        $this->db->where('tributo', $dados['tributo']);
        $id = $this->db->get($banco.'.dtb_parcelamento_nao_previdenciario_tributos_processo_negociados')->row()->id;
        return $id;
    }

    public function verifica_se_existe($cnpj, $banco, $id_processo, $tributo){
        $this->db->select('COUNT(distinct(dtb_parcelamento_nao_previdenciario_tributos_processo_negociados.id)) AS qtd, dtb_parcelamento_nao_previdenciario_tributos_processo_negociados.id AS id');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('id_processo', $id_processo);
        $this->db->where('tributo', $tributo);
        return $this->db->get($banco.'.dtb_parcelamento_nao_previdenciario_tributos_processo_negociados')->row();
    }
}
