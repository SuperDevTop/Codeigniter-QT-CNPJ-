<?php

class Simplesnacional_pedidos_parcelamentos_model extends CI_Model {

    public $id;
    public $cnpj;
    public $numero;
    public $data_pedido;
    public $situacao;
    public $data_situacao;
    public $observacao;
    public $path_recibo_adesao;
    public $valor_total_consolidado;
    public $qtd_parcelas;
    public $primeira_parcela;
    public $parcela_basica;
    public $data_consolidacao;
    public $path_parcela;


    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_simplesnacional_pedidos_parcelamentos', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $dados['cnpj'];
        $this->numero = $dados['numero'];
        $this->data_pedido = $dados['data_pedido'];
        $this->situacao = $dados['situacao'];
        $this->data_situacao = $dados['data_situacao'];
        $this->observacao = $dados['observacao'];
        // $this->path_recibo_adesao = $dados['path_recibo_adesao'];
        $this->valor_total_consolidado = $dados['valor_total_consolidado'];
        $this->qtd_parcelas = $dados['qtd_parcelas'];
        $this->primeira_parcela = $dados['primeira_parcela'];
        $this->parcela_basica = $dados['parcela_basica'];
        $this->data_consolidacao = $dados['data_consolidacao'];

        $this->db->insert($banco.'.dtb_ecac_simplesnacional_pedidos_parcelamentos', $this);
        return $this->db->insert_id();
    }

    public function verifica_se_existe($cnpj, $banco, $numero){
        $this->db->select('COUNT(distinct(dtb_ecac_simplesnacional_pedidos_parcelamentos.id)) AS qtd, dtb_ecac_simplesnacional_pedidos_parcelamentos.id as id');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        return $this->db->get($banco.'.dtb_ecac_simplesnacional_pedidos_parcelamentos')->row();
    }

    public function update($cnpj, $banco, $numero,  $situacao){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('situacao', $situacao);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        $this->db->update($banco.'.dtb_ecac_simplesnacional_pedidos_parcelamentos');
    }

    public function update_path_parcela($cnpj, $banco, $numero,  $path){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('path_parcela', $path);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        $this->db->update($banco.'.dtb_ecac_simplesnacional_pedidos_parcelamentos');
    }
}
