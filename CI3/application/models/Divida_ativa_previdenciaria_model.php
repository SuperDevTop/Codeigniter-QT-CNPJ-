<?php

class Divida_ativa_previdenciaria_model extends CI_Model {
    public  $cnpj;
    public  $numero_inscricao;
    public  $cnpj_devedor_principal;
    public  $devedor_principal;
    public  $fase_atual;
    public  $valor_total_debito;

    public function clear($idProcesso, $banco){
        return $this->db->delete($banco.'.dtb_ecac_divida_ativa_previdenciaria', "cnpj = {$idProcesso}");
    }

    public function insert($divida, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $divida['cnpj'];
        $this->numero_inscricao = $divida['numero_inscricao'];
        $this->cnpj_devedor_principal = $divida['cnpj_devedor_principal'];
        $this->devedor_principal = $divida['devedor_principal'];
        $this->fase_atual = $divida['fase_atual'];
        $this->valor_total_debito = $divida['valor_total_debito'];

        $this->db->insert($banco.'.dtb_ecac_divida_ativa_previdenciaria', $this);
        return $this->db->insert_id();
    }
}
