<?php

class Socios_empresas_model extends CI_Model {

    public function insere_socio_empresa($banco, $cnpj, $cpf_cnpj, $nome_socio, $situacao, $qualificacao){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj_empresa' => $cnpj,
                'cpf_cnpj' => $cpf_cnpj,
                'nome_socio' => $nome_socio,
                'situacao' => $situacao,
                'qualificacao' => $qualificacao,
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        $this->db->insert($banco.'.dtb_socios_empresas', $dados);
        return $this->db->insert_id();
    }


    public function busca_situacao_fiscal($banco){
        $this->db->select('DISTINCT(d.cnpj_data) as cnpj, d.caminho_download');
        $this->db->where('d.cnpj_data like "%0001%" ');
        $this->db->group_by('d.cnpj_data');
        return $this->db->get($banco.'.dtb_situacao_fiscal d')->result();
    }


    public function busca_socios($cnpj){
        $this->db->select('nome_socio, cpf_cnpj');
        $this->db->where('cnpj_empresa', $cnpj);
        return $this->db->get('dtb_socios_empresas')->result();
    }

    public function busca_socios_outras_empresas($cnpj, $socios){
        $this->db->select('*');
        $this->db->where('cnpj_empresa != ', $cnpj);
        $this->db->where_in('cpf_cnpj', $socios);
        $this->db->group_by('cnpj_empresa');
        return $this->db->get('dtb_socios_empresas')->result();
    }


    public function busca_socios_todas_empresas($socios){
        $this->db->select('db.cnpj_empresa, dtbe.razao_social');
        $this->db->join('dtb_empresas dtbe','db.cnpj_empresa = dtbe.cnpj');
        $this->db->where_in('db.cpf_cnpj', $socios);
        $this->db->group_by('db.cnpj_empresa');
        return $this->db->get('dtb_socios_empresas db')->result();
    }


    public function limpa_tabela($banco, $cnpj){
        return $this->db->delete($banco.'.dtb_socios_empresas', "cnpj_empresa = {$cnpj}");
    }

}
