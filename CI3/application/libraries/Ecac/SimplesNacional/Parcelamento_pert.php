<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Ecac/Ecac.php');

class Parcelamento_pert extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    function obter_parcelamento()
    {
        $cookie_complemento = 'cw_aplicacao=1.7; cw_sisobrapref=1.7';

        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx', array(""), '', false, $cookie_complemento );
        $html = new Simple_html_dom();
        $html->load($page);

        $__EVENTTARGET = 'ctl00$contentPlaceH$linkButtonConsulta';
        $__EVENTARGUMENT = '';
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if (!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__VIEWSTATE' => $__VIEWSTATE,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR
        );

        $page = $this->post($post_fields, $cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $pedidos = array();
        $table = $html->find('table[id=ctl00_contentPlaceH_ucListaParcelamento_gdv]', 0);
        if ($table) {
            $trs = $table->find('tr');
            $posicao  = 0;
            foreach ($trs as $tr) {
                if ($posicao > 0) {
                    $status = trim($tr->find('td', 2)->plaintext);
                    if ($status == 'Em parcelamento') {
                        $__EVENTTARGET = 'ctl00$contentPlaceH$ucListaParcelamento$gdv';
                        $__EVENTARGUMENT = 'Select$' . ($posicao - 1);
                        $nodes = $html->find("input[type=hidden]");
                        $vals = array();
                        foreach ($nodes as $node) {
                            $val = $node->value;
                            if (!empty($val) && !is_null($val))
                                $vals[] = $val;
                        }

                        $__VIEWSTATE = $vals[0];
                        $__VIEWSTATEGENERATOR = $vals[1];

                        $post_fields = array(
                            '__EVENTTARGET' => $__EVENTTARGET,
                            '__EVENTARGUMENT' => $__EVENTARGUMENT,
                            '__VIEWSTATE' => $__VIEWSTATE,
                            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                            '__VIEWSTATEENCRYPTED' => ''
                        );

                        $page =  $this->post2($post_fields, $cookie_complemento);
                        $pedido = $this->obter_informacao_pedido($page);
                        $pedido['demonstrativo_pagamentos'] = $this->obter_demonstrativos($page);
                        $pedido['relacao_debitos_parcelas'] = $this->obter_relacao_debitos_parcelas($page, $pedido['numero'], $cookie_complemento);
                        $pedidos[] = $pedido;
                    }
                }
                $posicao++;
            }
        }

        return $pedidos;
    }

    private function obter_informacao_pedido($page)
    {
        $html = new Simple_html_dom();
        $html->load($page);

        $table = $html->find('table[id=ctl00_contentPlaceH_ucListaParcelamento_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;
        $pedido = array();

        $pedido_contribuinte = array();
        foreach ($trs as $tr) {
            if ($i > 0) {
                $numero = trim($tr->find('td')[0]->plaintext);
                $data_pedido = trim($tr->find('td')[1]->plaintext);
                $situacao = trim($tr->find('td')[2]->plaintext);
                $data_situacao = trim($tr->find('td')[3]->plaintext);

                $pedido_contribuinte = array(
                    'numero' => $numero,
                    'data_pedido' => $data_pedido,
                    'situacao' => $situacao,
                    'data_situacao' => $data_situacao,
                );
                break;
            }
            $i++;
        }

        $table = $html->find('table[id=ctl00_contentPlaceH_ucConsolidacao_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;

        foreach ($trs as $tr) {
            if ($i > 0) {
                $valor_total_consolidado_entrada = trim($tr->find('td')[0]->plaintext);
                $quantidade_parcela = trim($tr->find('td')[1]->plaintext);
                $parcela_basica = trim($tr->find('td')[2]->plaintext);
                $valor_total_consolidado_divida = trim($tr->find('td')[3]->plaintext);
                $data_consolidacao = trim($tr->find('td')[4]->plaintext);

                $pedido = array_merge($pedido_contribuinte, array(
                    'valor_total_consolidado_entrada' => $valor_total_consolidado_entrada,
                    'qtd_parcelas' => $quantidade_parcela,
                    'parcela_basica' => $parcela_basica,
                    'valor_total_consolidado_divida' => $valor_total_consolidado_divida,
                    'data_consolidacao' => $data_consolidacao,
                ));

                break;
            }
            $i++;
        }


        return $pedido;
    }

    private function obter_demonstrativos($page)
    {
        $html = new Simple_html_dom();
        $html->load($page);
        $table = $html->find('table[id=ctl00_contentPlaceH_ucListaPagamento_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;
        $demonstrativos = array();
        foreach ($trs as $tr) {
            if ($i > 0) {
                $mes_parcela = trim($tr->find('td')[0]->plaintext);
                $vencimento_pert = trim($tr->find('td')[1]->plaintext);
                $data_arrecadacao = trim($tr->find('td')[2]->plaintext);
                $valor_pago = trim($tr->find('td')[3]->plaintext);

                $demonstrativos[] = array(
                    'mes_parcela' => $mes_parcela,
                    'vencimento_pert' => $vencimento_pert,
                    'data_arrecadacao' => $data_arrecadacao,
                    'valor_pago' => $valor_pago,
                );
            }
            $i++;
        }
        return $demonstrativos;
    }

    private function obter_relacao_debitos_parcelas($page, $numero_parcelamento, $cookie_complemento)
    {
        $html = new Simple_html_dom();
        $html->load($page);

        $__EVENTTARGET = 'ctl00$contentPlaceH$ucListaParcelamento$gdv';
        $__EVENTARGUMENT = 'Select$0';
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if (!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__VIEWSTATE' => $__VIEWSTATE,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
            '__VIEWSTATEENCRYPTED' => ''
        );

        $page = $this->post3($post_fields, $cookie_complemento);
        // $cnpj = $this->obter_numero_documento();
        // $page = $this->exec("https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/pertsn.app/Recibo.aspx?nroCnpj={$cnpj}&nroParcelamento={$numero_parcelamento}&nroConsolidacao=1&pagina=ConsultaPedidoDetalhes", array(""), '', false, $cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $table = $html->find("table[id=ctl00_contentPlaceH_wcListaDeb_gdv]", 0);
        $trs = $table->find('tr');
        $i = 0;
        $relacao_debitos_parcelas = array();
        foreach ($trs as $tr) {
            if ($i > 0) {
                $periodo_apuracao = trim($tr->find('td')[0]->plaintext);
                $vencimento = trim($tr->find('td')[1]->plaintext);
                $numero_processo = trim($tr->find('td')[2]->plaintext);
                $saldo_devedor_original = trim($tr->find('td')[3]->plaintext);
                $valor_atualizado = trim($tr->find('td')[4]->plaintext);

                $relacao_debitos_parcelas[] = array(
                    'periodo_apuracao' => $periodo_apuracao,
                    'vencimento' => $vencimento,
                    'numero_processo' => $numero_processo,
                    'saldo_devedor_original' => $saldo_devedor_original,
                    'valor_atualizado' => $valor_atualizado,
                );
            }
            $i++;
        }

        return $relacao_debitos_parcelas;
    }

    private function post($post_fields, $cookie_complemento)
    {

        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx";

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: private",
            'sec-ch-ua: " Not;A Brand";v="99", "Google Chrome";v="91", "Chromium";v="91"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.101 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true, $cookie_complemento);;
        
        return $resp;
    }

    private function post2($post_fields, $cookie_complemento)
    {
        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/ConsultaPedido.aspx";

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
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true, $cookie_complemento);

        return $resp;
    }

    private function post3($post_fields, $cookie_complemento)
    {
        $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/pertsn.app/ConsultaPedidoDetalhes.aspx";

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
            "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/pertsn.app/ConsultaPedidoDetalhes.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true, $cookie_complemento);

        return $resp;
    }

    //BUSCAR PARCELAS
    function obter_parcelas_pert()
    {
        $cookie_complemento = 'cw_aplicacao=1.7; cw_sisobrapref=1.7';

        // if (strlen($this->numero_documento_procuracao) < 12){
        //     return;
        // }
        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx', array(""), '', false, $cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);

        $__EVENTTARGET = 'ctl00$contentPlaceH$linkButtonEmitirDAS';
        $__EVENTARGUMENT = '';
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if (!empty($val) && !is_null($val))
                $vals[] = $val;
        }

        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__VIEWSTATE' => $__VIEWSTATE,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR
        );

        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx";

        $page = $this->exec($url, array(""), http_build_query($post_fields), true, $cookie_complemento);
        $html = $html->load($page);

        $div_principal = $html->find('table[id=ctl00_contentPlaceH_gdvParcelas]', 0);

        $parcelas = array();
        if ($div_principal) {
            $linhas = $div_principal->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            foreach ($linhas as $linha) {
                $data_parcela = $linha->find('td', 0)->plaintext;
                $valor = $linha->find('td', 1)->plaintext;
                $parcelas[] = array(
                    'valor' => str_replace('R$ ', '', str_replace(',', '.', $valor)),
                    'data_parcela' => $data_parcela
                );
            }
        }

        if (count($parcelas) > 0)
            return $parcelas;
        return false;
    }

    //GERAR PARCELA
    // function gerar_parcela_pert($data_emissao){
    //     $resultado = $this->download_parcela_pert($data_emissao);
    //     if (!$resultado)
    //         return 'ERROECAC';
    //     return $resultado;
    // }
    
    // function download_parcela_pert($data_parcela){
    //     $ch = curl_init();
    //     $caminho_salvar = str_replace('/var/www/html/', '', $this->caminho_da_pasta_pdfs);
    //     $caminho_salvar_extra = str_replace('//', '/', $caminho_salvar);
    //     $raw = http_build_query(array(
    //         'cookiecav' => $this->get_COOKIECAV(),
    //         'aspsession' => $this->get_ASPSESSION(),
    //         'data_parcela' => $data_parcela,
    //         'pasta' => $caminho_salvar_extra
    //     ));
    //     curl_setopt($ch, CURLOPT_URL, "http://34.139.11.176/index.php/Ecac/gerar_parcela_pert?{$raw}");
    //     curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $response = curl_exec($ch);
    //     if ( $response )
    //         return $response;
    //     return false;
    // }
    function gerar_parcela_pert($data_emissao){
        $cookie_complemento = 'cw_aplicacao=1.7; cw_sisobrapref=1.7';

        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx', array(""), '', false, $cookie_complemento);
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
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR);
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
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
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx",
            "Accept-Language: en-US,en;q=0.9",
        );
        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx', $headers, http_build_query($post_fields), true,$cookie_complemento);

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

        $data =  http_build_query(array(
            "__EVENTTARGET" => "",
            "__EVENTARGUMENT" => "",
            "__VIEWSTATE" => $__VIEWSTATE,
            "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
            'ctl00$contentPlaceH$btnContinuar' => "Continuar"

        ));
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
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
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/default.aspx",
            "Accept-Language: en-US,en;q=0.9",
         );


        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pertsn.app/ReemitirDas.aspx', $headers, $data, true, $cookie_complemento);
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

        $data =  array(
            "__EVENTTARGET" => 'ctl00$contentPlaceH$gdvParcela$ctl02$LinkButtonEmitirDas',
            "__EVENTARGUMENT" => "",
            "__VIEWSTATE" => $__VIEWSTATE,
            "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
        );
        $table = $html->find('table[id=ctl00_contentPlaceH_gdvParcela]', 0);
        $i = 0;
        if($table){
                foreach ($table->find('tr') as $tr){
                    if ($i > 0){
                        $data_linha = trim($tr->find('td',0)->plaintext);
                        if ($data_linha == $data_emissao){
                            $id_parcela = trim($tr->find('a',0)->id);
                            $__EVENTTARGET = str_replace('_', '$', $id_parcela);
                            $data['__EVENTTARGET'] = $__EVENTTARGET;
                            return $this->baixar_parcela_pert(http_build_query($data), $cookie_complemento);
                        }
                    }
                    $i++;
                }
            }
            return '';
    }

    function baixar_parcela_pert($post_str, $cookie_complemento){
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');
        
        $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/pertsn.app/ReemitirDas.aspx";

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
            "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/pertsn.app/ReemitirDas.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $content = $this->exec($url, $headers, $post_str, true, $cookie_complemento);

        $caminho_local = $this->caminho_da_pasta_pdfs."PGDASD-PERT-{$data_atual}.pdf";
        
        $aux_dir_ext = str_replace(FCPATH, "",$caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ( $this->verifica_pdf_valido($content)){
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/".$aux_dir_ext;
        }
        return $retorno;
    }
}
