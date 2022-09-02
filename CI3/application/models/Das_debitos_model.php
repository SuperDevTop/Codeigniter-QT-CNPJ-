<?php

class Das_debitos_model extends CI_Model {

    public  $id;
    public  $cnpj;
    public  $periodo_apuracao;
    public  $data_vencimento;
    public  $debito_declarado;
    public  $principal;
    public  $multa;
    public  $juros;
    public  $total;
    public  $exigibilidade_suspensa;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_das_debitos', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $dados['cnpj'];
        $this->periodo_apuracao = $dados['periodo_apuracao'];
        $this->data_vencimento = $dados['data_vencimento'];
        $this->debito_declarado = $dados['debito_declarado'];
        $this->principal = $dados['principal'];
        $this->multa = $dados['multa'];
        $this->juros = $dados['juros'];
        $this->total = $dados['total'];
        $this->exigibilidade_suspensa = $dados['exigibilidade_suspensa'];

        $this->db->insert($banco.'.dtb_ecac_das_debitos', $this);
        return $this->db->insert_id();
    }

    public function get($banco){
        $this->db->select('*');
        $this->db->from($banco.'.dtb_ecac_das_debitos');
        return $this->db->get()->result();
    }
}
