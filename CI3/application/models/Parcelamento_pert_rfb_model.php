<?php

class Parcelamento_pert_rfb_model extends CI_Model {
    public $id;
    public $cnpj;
    public $id_formatado;
    public $nome_modalidade;
    public $situacao;
    public $data_requerimento;
    public $data_concessao;
    public $data_consolidacao;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_pert_rfb', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $dados->cnpj;
        $this->id_formatado = $dados->idFormatado;
        $this->nome_modalidade = $dados->nomeExibicaoModalidade;
        $this->situacao = $dados->situacao;
        $this->data_requerimento = $dados->detalhesParc[0]->valor;
        $this->data_concessao = $dados->detalhesParc[1]->valor;
        $this->data_consolidacao = $dados->detalhesParc[2]->valor;

        $this->db->insert($banco.'.dtb_parcelamento_pert_rfb', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('situacao', $dados->situacao);

        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('id_formatado', $dados->idFormatado);

        $this->db->update($banco.'.dtb_parcelamento_pert_rfb');
    }

    public function verifica_se_existe($dados, $banco){
        $this->db->select('COUNT(distinct(dtb_parcelamento_pert_rfb.id)) AS qtd , dtb_parcelamento_pert_rfb.id AS id');
       
        $this->db->where('cnpj', $dados->cnpj);
        $this->db->where('id_formatado', $dados->idFormatado);

        return $this->db->get($banco.'.dtb_parcelamento_pert_rfb')->row();
    }
}
