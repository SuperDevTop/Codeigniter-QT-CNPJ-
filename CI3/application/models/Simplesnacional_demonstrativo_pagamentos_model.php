<?php

class Simplesnacional_demonstrativo_pagamentos_model extends CI_Model {

    public  $id;
    public  $id_parcelamento;
    public  $cnpj;
    public  $mes_parcela;
    public  $vencimento_das;
    public  $data_arrecadacao;
    public  $valor_pago;
    public  $path_recibo;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_simplesnacional_demonstrativo_pagamentos', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->id_parcelamento = $dados['id_parcelamento'];
        $this->cnpj = $dados['cnpj'];
        $this->mes_parcela = $dados['mes_parcela'];
        $this->vencimento_das = $dados['vencimento_das'];
        $this->data_arrecadacao = $dados['data_arrecadacao'];
        $this->valor_pago = $dados['valor_pago'];
        if (isset($dados['path_recibo'])){
            $this->path_recibo = $dados['path_recibo'];
        }


        $this->db->insert($banco.'.dtb_ecac_simplesnacional_demonstrativo_pagamentos', $this);
        return $this->db->insert_id();
    }

    public function verifica_se_existe($cnpj, $banco, $id_parcelamento, $mes_parcela){
        $this->db->select('COUNT(distinct(dtb_ecac_simplesnacional_demonstrativo_pagamentos.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('id_parcelamento', $id_parcelamento);
        $this->db->where('mes_parcela', $mes_parcela);
        return $this->db->get($banco.'.dtb_ecac_simplesnacional_demonstrativo_pagamentos')->row();
    }

}
