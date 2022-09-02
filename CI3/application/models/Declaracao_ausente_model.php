<?php

class Declaracao_ausente_model extends CI_Model {

    private $cnpj;

    public function busca_situacao_fiscal($banco){
        $this->db->select('DISTINCT(d.cnpj_data) as cnpj, d.caminho_download');
        $this->db->group_by('d.cnpj_data');
        return $this->db->get($banco.'.dtb_situacao_fiscal d')->result();
    }

}
