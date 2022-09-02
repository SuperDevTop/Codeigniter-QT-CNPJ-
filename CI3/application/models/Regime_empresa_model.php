<?php

class Regime_empresa_model extends CI_Model {

    private $cnpj;

    public function busca_situacao_fiscal($banco){
        $this->db->select('DISTINCT(cnpj_data) as cnpj, caminho_download');
        // $this->db->where('cnpj_data = "12254087000102" ');
        return $this->db->get($banco.'.dtb_situacao_fiscal')->result();
    }

    public function update($cnpj, $banco, $regime){
        
        $dados = array( 

                'tipo_regime' => $regime
        );
    
        if ($this->db->update($banco.'.dtb_empresas', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }

    }


    public function existe_excluida_simples($banco, $cnpj){
        $this->db->select('COUNT(distinct(dtb_empresas_excluidas_simples.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_empresas_excluidas_simples')->row();
    }

    public function existe_excluida_mei($banco, $cnpj){
        $this->db->select('COUNT(distinct(dtb_empresas_excluidas_mei.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_empresas_excluidas_mei')->row();
    }


    public function insere_exclusao_simples($data_inicio, $data_fim, $banco, $cnpj, $foi_excluido_simples){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'data_execucao' => date('Y-m-d H:i:s'),
                'cnpj' => $cnpj,
                'is_excluida' => $foi_excluido_simples
        );

        $this->db->insert($banco.'.dtb_empresas_excluidas_simples', $dados);
        return $this->db->insert_id();
    }

    public function atualiza_exclusao_simples($data_inicio, $data_fim, $banco, $cnpj, $foi_excluido_simples){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'data_execucao' => date('Y-m-d H:i:s'),
                'cnpj' => $cnpj,
                'is_excluida' => $foi_excluido_simples
        );
    
        if ($this->db->update($banco.'.dtb_empresas_excluidas_simples', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public function insere_exclusao_mei($data_inicio, $data_fim, $banco, $cnpj, $foi_excluido_mei){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'data_execucao' => date('Y-m-d H:i:s'),
                'cnpj' => $cnpj,
                'is_excluida' => $foi_excluido_mei
        );

        $this->db->insert($banco.'.dtb_empresas_excluidas_mei', $dados);
        return $this->db->insert_id();
    }

    public function atualiza_exclusao_mei($data_inicio, $data_fim, $banco, $cnpj, $foi_excluido_mei){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'data_execucao' => date('Y-m-d H:i:s'),
                'cnpj' => $cnpj,
                'is_excluida' => $foi_excluido_mei
        );
    
        if ($this->db->update($banco.'.dtb_empresas_excluidas_mei', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function update_simples($banco, $cnpj){
        $this->db->set('tipo_regime', 'SIMPLES NACIONAL');
        $this->db->where('cnpj', $cnpj);
        $this->db->update($banco.'.dtb_empresas');
    }

    public function update_normal($banco, $cnpj){
        $this->db->set('tipo_regime', 'NORMAL');
        $this->db->where('cnpj', $cnpj);
        $this->db->update($banco.'.dtb_empresas');
    }
}
