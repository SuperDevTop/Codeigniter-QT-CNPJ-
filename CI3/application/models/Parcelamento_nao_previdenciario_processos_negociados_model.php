<?php

class Parcelamento_nao_previdenciario_processos_negociados_model extends CI_Model {

    public $id;
    public $cnpj;
    public $processo;
    public $data_do_deferimento;
    public $situacao;


    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_parcelamento_nao_previdenciario_processos_negociados', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $dados['cnpj'];
        $this->processo = $dados['processo'];
        $this->data_do_deferimento = $dados['data_do_deferimento'];
        $this->situacao = $dados['situacao'];

        $this->db->insert($banco.'.dtb_parcelamento_nao_previdenciario_processos_negociados', $this);
        return $this->db->insert_id();
    }

    public function update($cnpj, $banco, $processo,  $situacao){
        date_default_timezone_set('America/Sao_Paulo');
        $this->db->set('situacao', $situacao);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('processo', $processo);
        $this->db->update($banco.'.dtb_parcelamento_nao_previdenciario_processos_negociados');
    }

    public function verifica_se_existe($cnpj, $banco, $processo){
        $this->db->select('COUNT(distinct(dtb_parcelamento_nao_previdenciario_processos_negociados.id)) AS qtd , dtb_parcelamento_nao_previdenciario_processos_negociados.id AS id');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('processo', $processo);
        return $this->db->get($banco.'.dtb_parcelamento_nao_previdenciario_processos_negociados')->row();
    }
}
