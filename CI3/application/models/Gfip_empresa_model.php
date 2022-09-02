<?php

class Gfip_empresa_model extends CI_Model {

    private $cnpj;

    public function busca_situacao_fiscal($banco){
        $this->db->select('DISTINCT(d.cnpj_data) as cnpj, d.caminho_download');
        // $this->db->where('cnpj_data', '02549591000186');
        $this->db->group_by('d.cnpj_data');
        return $this->db->get($banco.'.dtb_situacao_fiscal d')->result();
    }

    public function verifica_se_existe($banco, $cnpj){
        $this->db->select('COUNT(distinct(dtb_pendencia_gfip.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_pendencia_gfip')->row();
    }


    //GFIP REGULAR
    public function atualiza_gfip_regular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '0',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        if ($this->db->update($banco.'.dtb_pendencia_gfip', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public function insere_gfip_regular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '0',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        $this->db->insert($banco.'.dtb_pendencia_gfip', $dados);
        return $this->db->insert_id();
    }


    //GFIP IRREGULAR
    public function atualiza_gfip_irregular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '1',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        if ($this->db->update($banco.'.dtb_pendencia_gfip', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public function insere_gfip_irregular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '1',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        $this->db->insert($banco.'.dtb_pendencia_gfip', $dados);
        return $this->db->insert_id();
    }


    //Insere detalhes do gfip na tabela dtb_pendencia_gfip_detalhe
    public function insere_detalhe_gfip($banco, $cnpj, $competencia, $fpas, $situacao, $rubrica, $valor){

        $dados = array( 

                'cnpj' => $cnpj,
                'competencia' => $competencia,
                'fpas' => $fpas,
                'situacao' => $situacao,
                'rubrica' => $rubrica,
                'valor' => $valor
        );
    
        $this->db->insert($banco.'.dtb_pendencia_gfip_detalhe', $dados);
        return $this->db->insert_id();
    }


    //Limpar tabela de detalhes para sempre inserir as pendencias novas
    public function limpa_detalhe_gfip($banco, $cnpj){
        return $this->db->delete($banco.'.dtb_pendencia_gfip_detalhe', "cnpj = {$cnpj}");
    }

}
