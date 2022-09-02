<?php

class Divida_ativa_nao_previdenciaria_model extends CI_Model {

    public  $cnpj;
    public  $numero_inscricao;
    public  $numero_processo;
    public  $cnpj_devedor_principal;
    public  $situacao;
    public  $valor_consolidado;
    public  $data_consolidacao;
    public  $extinta;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_divida_ativa_nao_previdenciaria', "cnpj = {$cnpj}");
    }

    public function insert($divida, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $divida['cnpj'];
        $this->numero_inscricao = $divida['numero_inscricao'];
        $this->numero_processo = $divida['numero_processo'];
        $this->cnpj_devedor_principal = $divida['cnpj_devedor_principal'];
        $this->situacao = $divida['situacao'];
        $this->valor_consolidado = isset($divida['valor_consolidado']) ? $divida['valor_consolidado'] : '';
        $this->data_consolidacao = isset($divida['data_consolidacao']) ? $divida['data_consolidacao'] : '';
        $this->extinta = $divida['extinta'];

        $this->db->insert($banco.'.dtb_ecac_divida_ativa_nao_previdenciaria', $this);
        return $this->db->insert_id();
    }
}
