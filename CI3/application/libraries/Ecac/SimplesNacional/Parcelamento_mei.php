<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Parcelamento_mei extends Ecac
{
    private $cookie_complemento = 'cw_aplicacao=1.8; cw_sisobrapref=1.8';

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    function obter_parcelamentos(){
        $page = $this->exec('https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=134&origem=menu', array(''), '', false, $this->cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-site",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://cav.receita.fazenda.gov.br/",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

       
        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx', $headers, '', false, $this->cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $__EVENTTARGET = 'ctl00$contentPlaceH$linkButtonConsulta';
        $__EVENTARGUMENT = '';
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__VIEWSTATE' => $__VIEWSTATE ,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR
        );

        $page = $this->post($post_fields);
        

        $html = new Simple_html_dom();
        $html->load($page);

        $pedidos = array();

        $table = $html->find('table[id=ctl00_contentPlaceH_wcParc_gdv]',0);

        if ($table){
            $trs = $table->find('tr');
            $posicao  = 0;
            foreach ($trs as $tr){
                if ($posicao > 0){
                    $status = trim($tr->find('td', 2)->plaintext);
                    if ($status == 'Em parcelamento'){
                        $__EVENTTARGET = 'ctl00$contentPlaceH$wcParc$gdv';
                        $__EVENTARGUMENT = 'Select$'. ($posicao-1);
                        $nodes = $html->find("input[type=hidden]");
                        $vals = array();
                        foreach ($nodes as $node) {
                            $val = $node->value;
                            if(!empty($val) && !is_null($val))
                                $vals[] = $val;
                        }

                        $__VIEWSTATE = $vals[0];
                        $__VIEWSTATEGENERATOR = $vals[1];

                        $post_fields = array(
                            '__EVENTTARGET' => $__EVENTTARGET,
                            '__EVENTARGUMENT' => $__EVENTARGUMENT,
                            '__VIEWSTATE' => $__VIEWSTATE ,
                            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                            '__VIEWSTATEENCRYPTED' => '');
                        $html =  $this->post2($post_fields);
                        $pedido = $this->obter_informacao_pedido($html);
                        $pedido['consolidacao_original'] = $this->obter_consolidacao_original($html);
                        $pedido['demonstrativo_pagamento'] = $this->obter_demonstrativo($html);
                        $pedidos[] = $pedido;
                    }
                }
                $posicao++;
            }
        }

        return $pedidos;
    }

    private function obter_informacao_pedido($page){
        $html = new Simple_html_dom();
        $html->load($page);
        $table = $html->find('table[id=ctl00_contentPlaceH_wcParc_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;

        $pedido_contribuinte = array();
        foreach ($trs as $tr){
            if ( $i > 0){
                $pedido_contribuinte = array(
                    'numero' => trim($tr->find('td')[0]->plaintext),
                    'data_pedido' => trim($tr->find('td')[1]->plaintext),
                    'situacao' =>trim($tr->find('td')[2]->plaintext),
                    'data_situacao' => trim($tr->find('td')[3]->plaintext),
                );
                break;
            }
            $i++;
        }
        $table = $html->find('table[id=ctl00_contentPlaceH_wcConsol_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;

        foreach ($trs as $tr){
            if ( $i > 0){
                $pedido = array_merge($pedido_contribuinte , array(
                    'valor_total_consolidado' => trim($tr->find('td')[0]->plaintext),
                    'qtd_parcelas' => trim($tr->find('td')[1]->plaintext),
                    'parcela_basica' => trim($tr->find('td')[2]->plaintext),
                    'data_consolidacao' => trim($tr->find('td')[3]->plaintext)
                ));

                break;
            }
            $i++;
        }
        return $pedido;
    }

    private function obter_consolidacao_original($page){
        $html = new Simple_html_dom();
        $html->load($page);
        $__EVENTTARGET = 'ctl00$contentPlaceH$wcConsol$gdv';
        $__EVENTARGUMENT = 'Select$0';
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__VIEWSTATE' => $__VIEWSTATE ,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
            '__VIEWSTATEENCRYPTED' => ''
        );

        $page = $this->post3($post_fields);
        $html = new Simple_html_dom();
        $html->load($page);

        $table = $html->find("table[id=ctl00_contentPlaceH_wcConsol_wcListaDeb_gdv]", 0);
        $trs = $table->find('tr');
        $i = 0;
        $relacao_debitos_parcelas = array();
        foreach ($trs as $tr){
            if ( $i > 0 ){
                $relacao_debitos_parcelas[] = array(
                    'periodo_apurcao' => trim($tr->find('td')[0]->plaintext),
                    'vencimento' => trim($tr->find('td')[1]->plaintext),
                    'numero_processo' => trim($tr->find('td')[2]->plaintext),
                    'saldo_devedor_original' => trim($tr->find('td')[3]->plaintext),
                    'valor_atualizado' => trim($tr->find('td')[4]->plaintext),
                );
            }
            $i++;
        }

        return $relacao_debitos_parcelas;
    }

    private function obter_demonstrativo($page){
        $html = new Simple_html_dom();
        $html->load($page);
        $table = $html->find('table[id=ctl00_contentPlaceH_wcPagto_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;
        $demonstrativos = array();
        foreach ($trs as $tr){
            if ( $i > 0){
                $mes_parcela = trim($tr->find('td')[0]->plaintext);
                $vencimento_das = trim($tr->find('td')[1]->plaintext);
                $data_arrecadacao = trim($tr->find('td')[2]->plaintext);
                $valor_pago = trim($tr->find('td')[3]->plaintext);

                $demonstrativos[] = array(
                    'mes_parcela' => $mes_parcela,
                    'vencimento_das' => $vencimento_das,
                    'data_arrecadacao' => $data_arrecadacao,
                    'valor_pago' => $valor_pago,
                );
            }
            $i++;

        }
        return $demonstrativos;
    }

    private function post($post_fields){

        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx";

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true, $this->cookie_complemento);
        return $resp;
    }

    private function post2($post_fields){
        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/ConsultaPedido.aspx";

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );


        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true, $this->cookie_complemento);

        return $resp;
    }

    private function post3($post_fields){
        $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/atspo/parcmei.app/ConsultaPedidoDetalhes.aspx";

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/atspo/parcmei.app/ConsultaPedidoDetalhes.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $data = http_build_query($post_fields);
  
        $resp = $this->exec($url, $headers, $data, true, $this->cookie_complemento);

        return $resp;
    }

    // BUSCAR PARCELAS MEI
    function obter_parcelas_mei(){
        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx', array(''), '', false, $this->cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $__EVENTTARGET = 'ctl00$contentPlaceH$linkButtonEmitirDAS';
        $__EVENTARGUMENT = '';
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__VIEWSTATE' => $__VIEWSTATE ,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR
        );

        $page = $this->parcelas_mei_emissao_post($post_fields);
        $html = $html->load($page);

        $div_principal = $html->find('div[id=ctl00_contentPlaceH_pnlParcelas]', 0);

        $parcelas = array();
        if($div_principal){
            $linhas = $div_principal->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            foreach ($linhas as $linha){
                $valor = trim($linha->find('td', 1)->plaintext);
                $data_parcela = trim($linha->find('td', 0)->plaintext);
                $parcelas[] = array(
                    'valor' => str_replace('R$ ','',str_replace(',','.', $valor)),
                    'data_parcela' => $data_parcela);
            }
        }

        if(count($parcelas) > 0)
            return $parcelas;
        return false;

    }

    function parcelas_mei_emissao_post($post){
        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx";

        $response = $this->exec($url, array(""), $post, true, $this->cookie_complemento);

        return $this->converterCaracterEspecial($response);
    }
    // GERAR PARCELA MEI
    function gerar_parcela_mei($data_emissao)
    {
        $page = $this->exec('https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=134&origem=menu', array(""), '', false, $this->cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx', array(""), '', false, $this->cookie_complemento);
        $html->load($page);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            "__EVENTTARGET" => 'ctl00$contentPlaceH$linkButtonEmitirDAS',
            "__EVENTARGUMENT" => '',
            "__VIEWSTATE" => $__VIEWSTATE,
            "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
        );
        
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx',$headers, http_build_query($post_fields), true, $this->cookie_complemento);
        $html->load($page);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = http_build_query(array(
            "__EVENTTARGET" => '',
            "__EVENTARGUMENT" => '',
            "__VIEWSTATE" => $__VIEWSTATE,
            "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
            'ctl00$contentPlaceH$btnContinuar' => 'Continuar'
        ));
        
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/Default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
         );

        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/EmitirDAS.aspx";

        $page = $this->exec($url,$headers, $post_fields, true, $this->cookie_complemento);
        $html->load($page);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields =  array(
            "__EVENTTARGET" => 'ctl00$contentPlaceH$gdvParcelas$ctl02$LinkButtonEmitirDas',
            "__EVENTARGUMENT" => '',
            "__VIEWSTATE" => $__VIEWSTATE,
            "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
        );

        $table = $html->find('table[id=ctl00_contentPlaceH_gdvParcelas]', 0);
        $i = 0;
        foreach ($table->find('tr') as $tr){
            if ($i > 0){
                $data_linha = trim($tr->find('td',0)->plaintext);
                if ($data_linha == $data_emissao){
                    return $this->baixar_parcela_mei(http_build_query($post_fields));
                }
            }
            $i++;
        }
    }

    function baixar_parcela_mei($post_str)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/EmitirDAS.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/atspo/parcmei.app/EmitirDAS.aspx';
        $content = $this->exec($url, $headers, $post_str, true, $this->cookie_complemento);

        $caminho_local = $this->caminho_da_pasta_pdfs."MEI-{$data_atual}.pdf";

        $aux_dir_ext = str_replace(FCPATH, "",$caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ( $this->verifica_pdf_valido($content)){
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/".$aux_dir_ext;
        }
        return $retorno;
    }
}