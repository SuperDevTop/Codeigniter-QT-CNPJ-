<?php

class Limite_simples_model extends CI_Model {

    private $cnpj;
    private $valor_atual;
    private $percentual;
    private $data_execucao;

    public function busca_das_ano_corrente($banco, $cnpj){
        $ano_atual = date("Y");

        $this->db->select('caminho_download_declaracao, numero_declaracao');
        $this->db->where("compentencia like '%".$ano_atual."%'");
        $this->db->where("cnpj", $cnpj);
        $this->db->order_by("compentencia desc");
        return $this->db->get($banco.'.dtb_ecac_das')->row();
    }

    public function busca_empresas_das($banco){
        $this->db->select('DISTINCT(cnpj) as cnpj');
        return $this->db->get($banco.'.dtb_ecac_das')->result();
    }

    public function verifica_se_existe($cnpj, $banco){
        $this->db->select('COUNT(distinct(dtb_limite_simples.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco.'.dtb_limite_simples')->row();
    }

    public function insert($cnpj, $valor_atual, $percentual, $banco, $path){
        $valorAtualBanco = str_replace(".","",$valor_atual);
        $valorAtualBanco = str_replace(",",".",$valorAtualBanco);

        $valorPercentualBanco = str_replace(".","",$percentual);
        $valorPercentualBanco = str_replace(",",".",$valorPercentualBanco);

        date_default_timezone_set('America/Sao_Paulo');

        $ano_atual = date("Y");

        $dados = array(

            'valor_atual' => $valorAtualBanco,
            'percentual' => $valorPercentualBanco,
            'data_execucao' => date('Y-m-d H:i:s'),
            'cnpj' => $cnpj,
            'ano' => $ano_atual,
            'path' => $path
        );

        $this->db->insert($banco.'.dtb_limite_simples', $dados);
        return $this->db->insert_id();
    }

    public function update($cnpj, $valor_atual, $percentual, $banco, $path){
        $valorAtualBanco = str_replace(".","",$valor_atual);
        $valorAtualBanco = str_replace(",",".",$valorAtualBanco);

        $valorPercentualBanco = str_replace(".","",$percentual);
        $valorPercentualBanco = str_replace(",",".",$valorPercentualBanco);

        date_default_timezone_set('America/Sao_Paulo');
        $dados = array(

            'valor_atual' => $valorAtualBanco,
            'percentual' => $valorPercentualBanco,
            'data_execucao' => date('Y-m-d H:i:s'),
            'path' => $path
        );

        if ($this->db->update($banco.'.dtb_limite_simples', $dados, "cnpj=".$cnpj)){
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public function setar_campo_errado_nulo($banco, $numero_declaracao){

        $dados = array(

            'caminho_download_declaracao' => 'NULL',
            'caminho_download_recibo' => 'NULL',
            'caminho_download_extrato' => 'NULL'
        );

        if ($this->db->update($banco.'.dtb_ecac_das', $dados, "numero_declaracao=".$numero_declaracao)){
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
