<?php

class Dctf_notificacao_model extends CI_Model {

    public function dctf_nao_entregues_mes($banco){

        $sql = "";

        $sql_aux = "SELECT e.cnpj 
            FROM ".$banco.".dtb_empresas e 
            LEFT JOIN ".$banco.".dtb_ecac_dctf as d ON trim(e.cnpj) = trim(d.cnpj)
            WHERE LAST_DAY(DATE_ADD(NOW(), INTERVAL -2 MONTH)) >= DATE_FORMAT(STR_TO_DATE(d.periodo_inicial, '%d/%m/%Y'), '%Y-%m-%d') AND LAST_DAY(DATE_ADD(NOW(), INTERVAL -2 MONTH)) <= DATE_FORMAT(STR_TO_DATE(d.periodo_final, '%d/%m/%Y'), '%Y-%m-%d') AND (e.tipo_regime != 'SIMPLES NACIONAL' )  
            GROUP BY e.id";

        $sql1 = "SELECT e.id, e.cnpj_completo, e.razao_social, e.cnpj, d.periodo, d.tipo_status, d.numero_declaracao, d.numero_recibo, d.data_recepcao, 1 as sem_dctf, d.caminho_download_declaracao   
            FROM ".$banco.".dtb_empresas e 
            LEFT JOIN ".$banco.".dtb_ecac_dctf as d ON trim(e.cnpj) = trim(d.cnpj)
            WHERE LAST_DAY(DATE_ADD(NOW(), INTERVAL -2 MONTH)) >= DATE_FORMAT(STR_TO_DATE(d.periodo_inicial, '%d/%m/%Y'), '%Y-%m-%d') AND LAST_DAY(DATE_ADD(NOW(), INTERVAL -2 MONTH)) <= DATE_FORMAT(STR_TO_DATE(d.periodo_final, '%d/%m/%Y'), '%Y-%m-%d') AND (e.tipo_regime != 'SIMPLES NACIONAL' ) AND (d.tipo_status NOT LIKE '%Cancelada%')     
            GROUP BY e.id";

        $sql2 = "SELECT e.id, e.cnpj_completo, e.razao_social, e.cnpj, '' as periodo, '' as tipo_status, '' as numero_declaracao, '' as numero_recibo, '' as data_recepcao, 0 as sem_dctf, '' as caminho_download_declaracao  
            FROM ".$banco.".dtb_empresas e 
            WHERE cnpj not in (".$sql_aux.") AND (e.tipo_regime != 'SIMPLES NACIONAL' )  
            GROUP BY e.id";

        $sql = $sql1." UNION ".$sql2;

        $sql_final = 'SELECT * FROM ('.$sql.') as resultado where resultado.sem_dctf = 0 AND resultado.cnpj like "%0001%" ';

        return $this->db->query($sql_final)->result();

    }

    public function clear_notificacoes_mes($banco, $mes){
        return $this->db->delete($banco.'.dtb_notificacao_dctf_nao_entregue', "mes = '{$mes}'");
    }

    public function clear_notificacoes_proximo_vencer_mes($banco, $mes){
        return $this->db->delete($banco.'.dtb_notificacao_dctf_proxima_vencimento', "mes = '{$mes}'");
    }


    public function insere_notificacao($cnpj, $mes, $banco){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'mes' => $mes,
                'data' => date('Y-m-d H:i:s'),
                'descricao' => 'DCTF não entregue',
                'lida' => '0'
        );

        $this->db->insert($banco.'.dtb_notificacao_dctf_nao_entregue', $dados);
        return $this->db->insert_id();
    }


    public function insere_notificacao_proximo_vencer($cnpj, $mes, $banco){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'mes' => $mes,
                'data' => date('Y-m-d H:i:s'),
                'descricao' => 'DCTF próxima vencimento',
                'lida' => '0'
        );

        $this->db->insert($banco.'.dtb_notificacao_dctf_proxima_vencimento', $dados);
        return $this->db->insert_id();
    }

    public function buscar_empresas_sem_movimento($banco, $mes){
        $this->db->select('*');
        $this->db->where('mes = "'.$mes.'"');
        return $this->db->get($banco.'.dtb_dctf_sem_movimento')->result();
    }

}
