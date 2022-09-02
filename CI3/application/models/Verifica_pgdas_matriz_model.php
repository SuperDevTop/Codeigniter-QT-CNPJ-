<?php

class Verifica_pgdas_matriz_model extends CI_Model {

    private $cnpj;

    public function verifica_se_existe($banco, $cnpj){
        $this->db->select('COUNT(distinct(dtb_ausencia_dasn.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_ausencia_dasn')->row();
    }

    public function busca_empresas_sem_pgdas($banco){
        $sql_aux = "SELECT e.cnpj 
                FROM `".$banco."`.dtb_empresas e 
                JOIN `".$banco."`.dtb_ecac_das as d ON trim(e.cnpj) = trim(d.cnpj)
                WHERE (e.tipo_regime = 'SIMPLES NACIONAL') AND (e.situacao_cadastral != 'BAIXADO')  
                GROUP BY e.id";

        $sql2 = "SELECT e.cnpj 
                FROM `".$banco."`.dtb_empresas e 
                WHERE cnpj not in (".$sql_aux.") AND (e.tipo_regime = 'SIMPLES NACIONAL' )  AND (e.situacao_cadastral != 'BAIXADO')  AND (e.cnpj like '%0001%')
                GROUP BY e.id";


        return $this->db->query($sql2)->result();
    }

    public function busca_pgdas_filial($banco, $cnpj_base){
        $sql_aux = "SELECT * FROM `".$banco."`.dtb_ecac_das WHERE numero_declaracao like '%".$cnpj_base."%' ";

        return $this->db->query($sql_aux)->result();
    }

    public function insere_das($banco, $p, $cnpj){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'compentencia' => $p->compentencia,
                'numero_declaracao' => $p->numero_declaracao,
                'data_hora_transmissao' => $p->data_hora_transmissao,
                'numero_das' => $p->numero_das,
                'data_hora_emissao' => $p->data_hora_emissao,
                'pago' => $p->pago,
                'caminho_download_recibo' => $p->caminho_download_recibo,
                'caminho_download_declaracao' => $p->caminho_download_declaracao,
                'caminho_download_extrato' => $p->caminho_download_extrato
        );
    
        $this->db->insert($banco.'.dtb_ecac_das', $dados);
        return $this->db->insert_id();
    }

}
