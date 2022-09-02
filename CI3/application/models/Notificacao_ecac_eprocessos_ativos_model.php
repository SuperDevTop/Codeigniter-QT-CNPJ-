<?php

class Notificacao_ecac_eprocessos_ativos_model extends CI_Model {

    public $cnpj;
    public $numero_processo;
    public $data;
    public $descricao;
    public $lida;

    public function insere_notificacao($cnpj, $processo, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $cnpj;
        $this->numero_processo = $processo->numeroProcessoProcedimentoFormatado;
        $this->data = date('Y-m-d H:i:s');
        $this->lida = 0;
        $this->descricao = "Nova alteração no e-Processo  nº ".$processo->numeroProcessoProcedimentoFormatado;

        $this->db->insert($banco.'.dtb_notificacao_ecac_eprocessos_ativos', $this);
        return $this->db->insert_id();
    }

}