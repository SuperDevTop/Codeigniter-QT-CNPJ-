<?php

class Pgdas_notificacao_model extends CI_Model {

    public function pgdas_nao_entregues_mes($banco){

        $sql = "";

        $sql_aux = "SELECT e.cnpj 
            FROM ".$banco.".dtb_empresas e 
            LEFT JOIN ".$banco.".dtb_ecac_das as d ON trim(e.cnpj) = trim(d.cnpj)
            WHERE LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) >= DATE_FORMAT(STR_TO_DATE(CONCAT('01/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) <= DATE_FORMAT(STR_TO_DATE(CONCAT('31/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND (e.tipo_regime = 'SIMPLES NACIONAL') AND (e.situacao_cadastral != 'BAIXADO')  
            GROUP BY e.id";

        $sql1 = "SELECT e.id, e.cnpj_completo, e.razao_social, e.cnpj, d.compentencia, d.numero_declaracao, d.data_hora_transmissao, d.numero_das, d.data_hora_emissao, 1 as sem_pgdas, d.pago, d.caminho_download_recibo, d.caminho_download_declaracao, d.caminho_download_extrato    
            FROM ".$banco.".dtb_empresas e 
            LEFT JOIN ".$banco.".dtb_ecac_das as d ON trim(e.cnpj) = trim(d.cnpj)
            WHERE LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) >= DATE_FORMAT(STR_TO_DATE(CONCAT('01/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) <= DATE_FORMAT(STR_TO_DATE(CONCAT('31/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND (e.tipo_regime = 'SIMPLES NACIONAL' ) AND (e.situacao_cadastral != 'BAIXADO')  
            GROUP BY e.id";

        $sql2 = "SELECT e.id, e.cnpj_completo, e.razao_social, e.cnpj, '' as compentencia, '' as numero_declaracao, '' as data_hora_transmissao, '' as numero_das, '' as data_hora_emissao, 0 as sem_pgdas, '' as pago, '' as  caminho_download_recibo, '' as caminho_download_declaracao, '' as caminho_download_extrato 
            FROM ".$banco.".dtb_empresas e 
            WHERE cnpj not in (".$sql_aux.") AND (e.tipo_regime = 'SIMPLES NACIONAL' )  AND (e.situacao_cadastral != 'BAIXADO')  
            GROUP BY e.id";

        $sql = $sql1." UNION ".$sql2;

        $sql_final = 'SELECT * FROM ('.$sql.') as resultado where resultado.sem_pgdas = 0 AND resultado.cnpj like "%0001%" ';

        return $this->db->query($sql_final)->result();

    }


    public function das_nao_gerado_mes($banco){

        $sql = "";

        $sql1 = "SELECT e.id, e.cnpj_completo, e.razao_social, e.cnpj, d.compentencia, d.numero_declaracao, d.data_hora_transmissao, d.numero_das, d.data_hora_emissao, 1 as sem_pgdas, d.pago, d.caminho_download_recibo, d.caminho_download_declaracao, d.caminho_download_extrato    
            FROM ".$banco.".dtb_empresas e 
            LEFT JOIN ".$banco.".dtb_ecac_das as d ON trim(e.cnpj) = trim(d.cnpj)
            WHERE LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) >= DATE_FORMAT(STR_TO_DATE(CONCAT('01/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) <= DATE_FORMAT(STR_TO_DATE(CONCAT('31/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND (e.tipo_regime = 'SIMPLES NACIONAL' ) AND (e.situacao_cadastral != 'BAIXADO')  
            GROUP BY e.id";

       

        $sql = $sql1;

        $sql_final = 'SELECT * FROM ('.$sql.') as resultado where (resultado.numero_das is null OR resultado.numero_das = "") AND resultado.cnpj like "%0001%" ';

        return $this->db->query($sql_final)->result();

    }


    public function das_nao_pagos_mes($banco){

        $sql = "";

        $sql1 = "SELECT e.id, e.cnpj_completo, e.razao_social, e.cnpj, d.compentencia, d.numero_declaracao, d.data_hora_transmissao, d.numero_das, d.data_hora_emissao, 1 as sem_pgdas, d.pago, d.caminho_download_recibo, d.caminho_download_declaracao, d.caminho_download_extrato, d.caminho_download_das      
                FROM ".$banco.".dtb_empresas e 
                LEFT JOIN ".$banco.".dtb_ecac_das as d ON trim(e.cnpj) = trim(d.cnpj)
                WHERE LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) >= DATE_FORMAT(STR_TO_DATE(CONCAT('01/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)) <= DATE_FORMAT(STR_TO_DATE(CONCAT('31/',REPLACE(d.compentencia, 'PA ', '')), '%d/%m/%Y'), '%Y-%m-%d') AND (e.tipo_regime = 'SIMPLES NACIONAL' ) AND (e.situacao_cadastral != 'BAIXADO')  
                GROUP BY e.id";

        $sql = $sql1;

        $sql_final = 'SELECT * FROM ('.$sql.') as resultado where resultado.pago = "Não" AND resultado.cnpj like "%0001%" ';

        return $this->db->query($sql_final)->result();

    }

    public function clear_notificacoes_mes($banco, $mes){
        return $this->db->delete($banco.'.dtb_notificacao_pgdas_nao_entregue', "mes = '{$mes}'");
    }

    public function clear_notificacoes_proximo_vencer_mes($banco, $mes){
        return $this->db->delete($banco.'.dtb_notificacao_pgdas_proxima_vencimento', "mes = '{$mes}'");
    }

    public function clear_notificacoes_das_nao_gerado_mes($banco, $mes){
        return $this->db->delete($banco.'.dtb_notificacao_das_nao_gerado', "mes = '{$mes}'");
    }

    public function clear_notificacoes_das_nao_pagos_mes($banco, $mes){
        return $this->db->delete($banco.'.dtb_notificacao_das_nao_pagos', "mes = '{$mes}'");
    }

    public function insere_notificacao($cnpj, $mes, $banco){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'mes' => $mes,
                'data' => date('Y-m-d H:i:s'),
                'descricao' => 'PGDAS não entregue',
                'lida' => '0'
        );

        $this->db->insert($banco.'.dtb_notificacao_pgdas_nao_entregue', $dados);
        return $this->db->insert_id();
    }

    public function insere_notificacao_proximo_vencer($cnpj, $mes, $banco){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'mes' => $mes,
                'data' => date('Y-m-d H:i:s'),
                'descricao' => 'PGDAS próxima vencimento',
                'lida' => '0'
        );

        $this->db->insert($banco.'.dtb_notificacao_pgdas_proxima_vencimento', $dados);
        return $this->db->insert_id();
    }


    public function insere_notificacao_das_nao_gerado($cnpj, $mes, $banco){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'mes' => $mes,
                'data' => date('Y-m-d H:i:s'),
                'descricao' => 'DAS não gerado',
                'lida' => '0'
        );

        $this->db->insert($banco.'.dtb_notificacao_das_nao_gerado', $dados);
        return $this->db->insert_id();
    }


    public function insere_notificacao_das_nao_pago($cnpj, $mes, $banco){
        
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'cnpj' => $cnpj,
                'mes' => $mes,
                'data' => date('Y-m-d H:i:s'),
                'descricao' => 'DAS não pagos',
                'lida' => '0'
        );

        $this->db->insert($banco.'.dtb_notificacao_das_nao_pagos', $dados);
        return $this->db->insert_id();
    }


    public function buscar_empresas_sem_movimento($banco, $mes){
        $this->db->select('*');
        $this->db->where('mes = "'.$mes.'"');
        return $this->db->get($banco.'.dtb_pgdas_sem_movimento')->result();
    }

}
