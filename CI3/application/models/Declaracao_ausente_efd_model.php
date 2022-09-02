<?php

class Declaracao_ausente_efd_model extends CI_Model {

    private $cnpj;

    ///////////////////////////////////////////////AUSENCIA DE GFIP////////////////////////////////////////////////////////
    public function verifica_se_existe($banco, $cnpj){
        $this->db->select('COUNT(distinct(dtb_ausencia_efd.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_ausencia_efd')->row();
    }


    //AUSENCIA GFIP REGULAR
    public function atualiza_ausencia_efd_regular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '0',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        if ($this->db->update($banco.'.dtb_ausencia_efd', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public function insere_ausencia_efd_regular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '0',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        $this->db->insert($banco.'.dtb_ausencia_efd', $dados);
        return $this->db->insert_id();
    }


    //AUSENCIA GFIP IRREGULAR
    public function atualiza_ausencia_efd_irregular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '1',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        if ($this->db->update($banco.'.dtb_ausencia_efd', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public function insere_ausencia_efd_irregular($banco, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'possui_pendencia' => '1',
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
        $this->db->insert($banco.'.dtb_ausencia_efd', $dados);
        return $this->db->insert_id();
    }


    //Insere detalhes do gfip na tabela dtb_pendencia_gfip_detalhe
    public function insere_ausencia_efd_detalhe($banco, $cnpj, $tipo, $tipo_periodo, $periodo){

        $dados = array( 

                'cnpj' => $cnpj,
                'tipo' => $tipo,
                'tipo_periodo' => $tipo_periodo,
                'periodo' => $periodo
        );
    
        $this->db->insert($banco.'.dtb_ausencia_efd_detalhe', $dados);
        return $this->db->insert_id();
    }


    //Limpar tabela de detalhes para sempre inserir as pendencias novas
    public function limpa_ausencia_efd_detalhe($banco, $cnpj){
        return $this->db->delete($banco.'.dtb_ausencia_efd_detalhe', "cnpj = {$cnpj}");
    }

}
