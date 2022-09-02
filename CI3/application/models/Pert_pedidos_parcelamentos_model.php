<?php

class Pert_pedidos_parcelamentos_model extends CI_Model {

    public $id;
    public $cnpj;
    public $numero;
    public $data_pedido;
    public $situacao;
    public $data_situacao;
    public $valor_total_consolidado_entrada;
    public $qtd_parcelas;
    public $parcela_basica;
    public $valor_total_consolidado_divida;
    public $data_consolidacao;



    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_pert_pedidos_parcelamentos', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $dados['cnpj'];
        $this->numero = $dados['numero'];
        $this->data_pedido = $dados['data_pedido'];
        $this->situacao = $dados['situacao'];
        $this->data_situacao = $dados['data_situacao'];
        $this->valor_total_consolidado_entrada = $dados['valor_total_consolidado_entrada'];
        $this->qtd_parcelas = $dados['qtd_parcelas'];
        $this->parcela_basica = $dados['parcela_basica'];
        $this->valor_total_consolidado_divida = $dados['valor_total_consolidado_divida'];
        $this->data_consolidacao = $dados['data_consolidacao'];


        $this->db->insert($banco.'.dtb_ecac_pert_pedidos_parcelamentos', $this);
        return $this->db->insert_id();
    }

    public function verifica_se_existe($cnpj, $banco, $numero){
        $this->db->select('COUNT(distinct(dtb_ecac_pert_pedidos_parcelamentos.id)) AS qtd, dtb_ecac_pert_pedidos_parcelamentos.id as id');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        return $this->db->get($banco.'.dtb_ecac_pert_pedidos_parcelamentos')->row();
    }

    public function update($cnpj, $banco, $numero,  $situacao){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('situacao', $situacao);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero', $numero);
        $this->db->update($banco.'.dtb_ecac_pert_pedidos_parcelamentos');
    }

}
