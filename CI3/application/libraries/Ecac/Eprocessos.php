<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Eprocessos extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

      public function get_eprocessos_ativos(){
        $url = "https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos?solidario=false";

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: none",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
 

       $response = $this->exec($url, $headers);
        
        $dados = json_decode( $response, true );
        return $dados;
    }

    public function get_eprocessos_inativos(){
        $url = 'https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos/inativos';

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: none",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
       

        $response = $this->exec($url, $headers);
        

        $dados = json_decode( $response, true );
        return $dados;
    }

    public function get_eprocesso_historico($numero_processo){
        $url = 'https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos/'.$numero_processo.'/historico/';

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: none",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
       

        $response = $this->exec($url, $headers);
        $dados = json_decode( $response, true );
        return $dados;
    }


}