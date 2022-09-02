<?php

class Dctf_web_detalhes_model extends CI_Model
{
    public $id_declaracao;
    public $nivel;
    public $tributo;
    public $pa_debito;
    public $debito_apurado;
    public $credito_vinculado;
    public $saldo_a_pagar;

    public function insert($dados, $id, $banco)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $this->id_declaracao = $id;
        $this->nivel = $dados['nivel'];
        $this->tributo = $dados['tributo'];
        $this->pa_debito = $dados['pa_debito'];
        $this->debito_apurado = $dados['debito_apurado'];
        $this->credito_vinculado = $dados['credito_vinculado'];
        $this->saldo_a_pagar = $dados['saldo_a_pagar'];
      

        $this->db->insert($banco . '.dtb_ecac_dctf_web_detalhes', $this);
        return $this->db->insert_id();
    }

    public function clear($id, $banco){
        return $this->db->delete($banco.'.dtb_ecac_dctf_web_detalhes', "id_declaracao = {$id}");
    }
}
