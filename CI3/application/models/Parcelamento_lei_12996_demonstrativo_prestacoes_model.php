<?php

class Parcelamento_lei_12996_demonstrativo_prestacoes_model extends CI_Model {

    public $id;
    public $id_divida_consolidada;
    public $cnpj;
    public $parcela_id;
    public $data_parcela;
    public $valor_parc_minima;
    public $valor_parcela_divida;
    public $valor_parc_calculada;
    public $saldo_parc_devedora;
    public $juros_parc_deverdora;
    public $indicador_parcela_devida;
    public $indicador_situacao_parcela;
    public $indicador_reducao;
    public $valor_total_arrecadacao;
    public $valor_reducao_mes;
    public $valor_antecipacao_mes;
    public $quantidade_parc_red;
    public $path_download_parcela;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_lei_12996_demonstrativo_prestacoes', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->id_divida_consolidada = $dados['id_divida_consolidada'];
        $this->cnpj = $dados['cnpj'];
        $this->parcela_id = $dados['parcela_id'];
        $this->data_parcela = $dados['data_parcela'];
        $this->valor_parc_minima = $dados['valor_parc_minima'];
        $this->valor_parcela_divida = $dados['valor_parcela_divida'];
        $this->valor_parc_calculada = $dados['valor_parc_calculada'];
        $this->saldo_parc_devedora = $dados['saldo_parc_devedora'];
        $this->juros_parc_deverdora = $dados['juros_parc_deverdora'];
        $this->indicador_parcela_devida = $dados['indicador_parcela_devida'];
        $this->indicador_situacao_parcela = $dados['indicador_situacao_parcela'];
        $this->indicador_reducao = $dados['indicador_reducao'];
        $this->valor_total_arrecadacao = $dados['valor_total_arrecadacao'];
        $this->valor_reducao_mes = $dados['valor_reducao_mes'];
        $this->valor_antecipacao_mes = $dados['valor_antecipacao_mes'];
        $this->quantidade_parc_red = $dados['quantidade_parc_red'];

        $this->db->insert($banco.'.dtb_parcelamento_lei_12996_demonstrativo_prestacoes', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('valor_parc_minima', $dados['valor_parc_minima']);
        $this->db->set('valor_parcela_divida', $dados['valor_parcela_divida']);
        $this->db->set('valor_parc_calculada', $dados['valor_parc_calculada']);
        $this->db->set('saldo_parc_devedora', $dados['saldo_parc_devedora']);
        $this->db->set('juros_parc_deverdora', $dados['juros_parc_deverdora']);
        $this->db->set('indicador_parcela_devida', $dados['indicador_parcela_devida']);
        $this->db->set('indicador_situacao_parcela', $dados['indicador_situacao_parcela']);
        $this->db->set('indicador_reducao', $dados['indicador_reducao']);
        $this->db->set('valor_total_arrecadacao', $dados['valor_total_arrecadacao']);
        $this->db->set('valor_reducao_mes', $dados['valor_reducao_mes']);
        $this->db->set('valor_antecipacao_mes', $dados['valor_antecipacao_mes']);
        $this->db->set('quantidade_parc_red', $dados['quantidade_parc_red']);

        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('id_divida_consolidada', $dados['id_divida_consolidada']);
        $this->db->where('parcela_id', $dados['parcela_id']);

        $this->db->update($banco.'.dtb_parcelamento_lei_12996_demonstrativo_prestacoes');
    }

    public function update_path($path_download_parcela, $dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('path_download_parcela', $path_download_parcela);

        $this->db->where('cnpj', $dados['cnpj']);
        $this->db->where('id_divida_consolidada', $dados['id_divida_consolidada']);
        $this->db->where('parcela_id', $dados['parcela_id']);

        $this->db->update($banco.'.dtb_parcelamento_lei_12996_demonstrativo_prestacoes');
    }

    public function verifica_se_existe($cnpj, $banco, $parcela_id, $id_divida_consolidada){
        $this->db->select('COUNT(distinct(dtb_parcelamento_lei_12996_demonstrativo_prestacoes.id)) AS qtd, dtb_parcelamento_lei_12996_demonstrativo_prestacoes.id AS id, dtb_parcelamento_lei_12996_demonstrativo_prestacoes.path_download_parcela AS path_download_parcela');
        
        $this->db->where('cnpj', $cnpj);
        $this->db->where('parcela_id', $parcela_id);
        $this->db->where('id_divida_consolidada', $id_divida_consolidada);
        
        return $this->db->get($banco.'.dtb_parcelamento_lei_12996_demonstrativo_prestacoes')->row();
    }
}
