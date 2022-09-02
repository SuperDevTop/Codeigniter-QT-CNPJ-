<?php

class Parcelamento_lei_12996_divida_consolidada_model extends CI_Model {

    public $id;
    public $cnpj;
    public $cnpj_formatado;
    public $nome_empresarial;
    public $municipio;
    public $data_adesao;
    public $data_validacao;
    public $data_negociacao;
    public $data_efeito_exclusao;
    public $data_ciencia;
    public $data_encerramento;
    public $data_liquidacao_divida;
    public $data_exclusao;
    public $codigo_motivo_exclusao;
    public $in_solicitacao_reativacao;
    public $cod_fase;
    public $cod_modalidade;
    public $cod_situacao;
    public $nome_modalidade;
    public $nome_situacao;
    public $cod_receita;
    public $proximo_dia_util;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_lei_12996_divida_consolidada', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $dados['cnpj'];
        $this->cnpj_formatado = $dados['cnpj_formatado'];
        $this->nome_empresarial = $dados['nome_empresarial'];
        $this->municipio = $dados['municipio'];
        $this->data_adesao = $dados['data_adesao'];
        $this->data_validacao = $dados['data_validacao'];
        $this->data_negociacao = $dados['data_negociacao'];
        $this->data_efeito_exclusao = $dados['data_efeito_exclusao'];
        $this->data_ciencia = $dados['data_ciencia'];
        $this->data_encerramento = $dados['data_encerramento'];
        $this->data_liquidacao_divida = $dados['data_liquidacao_divida'];
        $this->data_exclusao = $dados['data_exclusao'];
        $this->codigo_motivo_exclusao = $dados['codigo_motivo_exclusao'];
        $this->in_solicitacao_reativacao = $dados['in_solicitacao_reativacao'];
        $this->cod_fase = $dados['cod_fase'];
        $this->cod_modalidade = $dados['cod_modalidade'];
        $this->cod_situacao = $dados['cod_situacao'];
        $this->nome_modalidade = $dados['nome_modalidade'];
        $this->nome_situacao = $dados['nome_situacao'];
        $this->cod_receita = $dados['cod_receita'];
        $this->proximo_dia_util = $dados['proximo_dia_util'];

        $this->db->insert($banco.'.dtb_parcelamento_lei_12996_divida_consolidada', $this);
        return $this->db->insert_id();
    }

    // public function update($cnpj, $banco, $data_adesao,  $nome_situacao){
    //     date_default_timezone_set('America/Sao_Paulo');
    //     $this->db->set('nome_situacao', $nome_situacao);
            
    //     $this->db->where('cnpj', $cnpj);
    //     $this->db->where('data_adesao', $data_adesao);

        
    //     $this->db->update($banco.'.dtb_parcelamento_lei_12996_divida_consolidada');
    // }
    public function update($dados, $id, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('nome_situacao', $dados['nome_situacao']);
        $this->db->set('proximo_dia_util', $dados['proximo_dia_util']);

        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('id', $id);
        
        $this->db->update($banco.'.dtb_parcelamento_lei_12996_divida_consolidada');
    }

    // public function verifica_se_existe($cnpj, $banco, $cod_modalidade){
    //     $this->db->select('COUNT(distinct(dtb_parcelamento_lei_12996_divida_consolidada.id)) AS qtd , dtb_parcelamento_lei_12996_divida_consolidada.id AS id');
        
    //     $this->db->where('cnpj', $cnpj);
    //     $this->db->where('cod_modalidade', $cod_modalidade);
        
    //     return $this->db->get($banco.'.dtb_parcelamento_lei_12996_divida_consolidada')->row();
    // }
    public function verifica_se_existe($dados, $banco){
        $this->db->select('COUNT(distinct(dtb_parcelamento_lei_12996_divida_consolidada.id)) AS qtd , dtb_parcelamento_lei_12996_divida_consolidada.id AS id');
        
        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('cod_modalidade', $dados['cod_modalidade']);
        $this->db->where('cod_receita', $dados['cod_receita']);
        
        return $this->db->get($banco.'.dtb_parcelamento_lei_12996_divida_consolidada')->row();
    }
}
