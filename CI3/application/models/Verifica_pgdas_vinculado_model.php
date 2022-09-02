<?php

class Verifica_pgdas_vinculado_model extends CI_Model {

    private $cnpj;

    public function verifica_se_existe($banco, $cnpj){
        $this->db->select('COUNT(distinct(dtb_ausencia_dasn.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_ausencia_dasn')->row();
    }

    public function busca_empresas_pgdas($banco){
        $this->db->select('*');
        return $this->db->get($banco.'.dtb_ecac_das')->result();
    }

    public function busca_empresa_certa($banco, $cnpj){
        $this->db->select('cnpj');
        $this->db->where('cnpj like "%'.$cnpj.'%"');
        $this->db->where('cnpj like "%0001%"');
        return $this->db->get($banco.'.dtb_empresas')->row();
    }


    public function atualiza_empresa_certa($banco, $r, $cnpj){

        $dados = array( 

                'cnpj' => $cnpj
        );
    
        if ($this->db->update($banco.'.dtb_ecac_das', $dados, "id=".$r->id)){
            return TRUE;
        } else {
            return FALSE;
        }
    }



}
