<?php

class Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model extends CI_Model {
    public $id;
    public $id_parcelamento;
    public $cnpj;
    public $parcelamento_foi_reconsolidado;
    public $simbolo_moeda;
    public $data_arrecadacao;
    public $valor_arrecadado;
    public $principal_utilizado;
    public $juros_utilizado;
    public $numero_documento;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_pagamentos', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->id_parcelamento = $dados->id_parcelamento;
        $this->cnpj = $dados->cnpj;
        $this->parcelamento_foi_reconsolidado = $dados->parcelamentoFoiReconsolidado;
        $this->simbolo_moeda = $dados->simboloMoeda;
        $this->data_arrecadacao = $dados->dataArrecadacao;
        $this->valor_arrecadado = $dados->valorArrecadado;
        $this->principal_utilizado = $dados->principalUtilizado;
        $this->juros_utilizado = $dados->jurosUtilizado;
        $this->numero_documento = $dados->numeroDocumento;

        $this->db->insert($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_pagamentos', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('parcelamento_foi_reconsolidado', $dados->parcelamentoFoiReconsolidado);

        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('numero_documento', $dados->numeroDocumento);

        $this->db->update($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_pagamentos');
    }

    public function verifica_se_existe($dados, $banco){
        $this->db->select('COUNT(distinct(dtb_parcelamento_pert_rfb_demonstrativo_de_pagamentos.id)) AS qtd, dtb_parcelamento_pert_rfb_demonstrativo_de_pagamentos.id AS id');
        
        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('numero_documento', $dados->numeroDocumento);
        
        return $this->db->get($banco.'.dtb_parcelamento_pert_rfb_demonstrativo_de_pagamentos')->row();
    }
}
