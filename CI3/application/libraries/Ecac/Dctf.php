<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Dctf extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function get_dctf($myhashmap){
        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
           "sec-ch-ua-mobile: ?0",
           'sec-ch-ua-platform: "Windows"',
           "Upgrade-Insecure-Requests: 1",
           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
           "Sec-Fetch-Site: none",
           "Sec-Fetch-Mode: navigate",
           "Sec-Fetch-User: ?1",
           "Sec-Fetch-Dest: document",
           "Accept-Language: pt-BR,pt;q=0.9",
        );
        // $page = $this->exec( 'https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/Abrir.asp', $headers);
        $post_fields = array(
            'ano' => '0',
            'consultar' => 'Consultar'
        );

        $page = $this->exec( 'https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/consulta.asp', $headers, http_build_query($post_fields), true);
        $html = new Simple_html_dom();
        $html->load($page);
        $tbDeclaracoes = $html->find('table[id=tbDeclaracoes]', 0);
        $declaracoes = array();
        if($tbDeclaracoes){
            $linhas = $tbDeclaracoes->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            foreach ($linhas as $linha){
                $periodo_inicial = trim( $linha->find('td', 3)->plaintext );
                $periodo_inicial = date('Y-m-d', strtotime(str_replace('/', '-', $periodo_inicial)));
                if($periodo_inicial < date('Y-m-d', strtotime('01-01-2020')))
                    continue;

                $tipo = trim( $linha->find('td', 6)->plaintext );
                $pos = strpos('Cancelada', $tipo);

                if ($pos === false) {

                } else {
                    continue;
                }

                if(isset($myhashmap[$this->apenas_numero(trim( $linha->find('td', 0)->plaintext ))."/".trim( $linha->find('td', 1)->plaintext )])){
                    continue;
                }

                $declaracoes[] = array(
                    'cnpj' => $this->apenas_numero(trim( $linha->find('td', 0)->plaintext )),
                    'cnpj_formatado' => trim( $linha->find('td', 0)->plaintext ),
                    'periodo' => trim( $linha->find('td', 1)->plaintext ),
                    'data_recepcao' => trim( $linha->find('td', 2)->plaintext ),
                    'periodo_inicial' => trim( $linha->find('td', 3)->plaintext ),
                    'periodo_final' => trim( $linha->find('td', 4)->plaintext ),
                    'situacao' => trim( $linha->find('td', 5)->plaintext ),
                    'tipo_status' => trim( $linha->find('td', 6)->plaintext ),
                    'numero_declaracao' =>  '',
                    'numero_recibo' => '',
                    'data_processamento' => '',
                );
            }
        }
        return $declaracoes;
    }

}