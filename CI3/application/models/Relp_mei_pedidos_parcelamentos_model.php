<?php

class Relp_mei_pedidos_parcelamentos_model extends CI_Model {

    public $id;
    public $cnpj;
    public $numero;
    public $data_pedido;
    public $situacao;
    public $data_situacao;
    public $path_recibo_adesao;
    public $valor_total_consolidado;
    public $qtd_parcelas;
    public $parcela_entrada;
    public $data_consolidacao;
    public $path_parcela;


    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_relp_mei_pedidos_parcelamentos', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $dados['cnpj'];
        $this->numero = $dados['numero'];
        $this->data_pedido = $dados['data_pedido'];
        $this->situacao = $dados['situacao'];
        $this->data_situacao = $dados['data_situacao'];

        $this->valor_total_consolidado = $dados['valor_total_consolidado'];
        $this->qtd_parcelas = $dados['qtd_parcelas'];
        $this->parcela_entrada = $dados['parcela_entrada'];
        $this->data_consolidacao = $dados['data_consolidacao'];

        $this->db->insert($banco.'.dtb_ecac_relp_mei_pedidos_parcelamentos', $this);
        return $this->db->insert_id();
    }

    public function verifica_se_existe($cnpj, $banco, $numero){
        $this->db->select('COUNT(distinct(dtb_ecac_relp_mei_pedidos_parcelamentos.id)) AS qtd, dtb_ecac_relp_mei_pedidos_parcelamentos.id as id');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        return $this->db->get($banco.'.dtb_ecac_relp_mei_pedidos_parcelamentos')->row();
    }

    public function update($cnpj, $banco, $numero,  $situacao){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('situacao', $situacao);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        $this->db->update($banco.'.dtb_ecac_relp_mei_pedidos_parcelamentos');
    }

    public function update_path_parcela($cnpj, $banco, $numero,  $path){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('path_parcela', $path);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        $this->db->update($banco.'.dtb_ecac_relp_mei_pedidos_parcelamentos');
    }
}
