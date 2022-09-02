<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Ecac/Ecac.php');


class Parcelamento extends Ecac
{
    private $cookie_complemento = 'cw_aplicacao=1.8; cw_sisobrapref=1.8';

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    function obter_parcelamento()
    {
        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx', array(''), '', false, $this->cookie_complemento);
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

        $page = $this->post($post_fields);

        $html = new Simple_html_dom();
        $html->load($page);
        $pedidos = array();

        $table = $html->find('table[id=ctl00_contentPlaceH_wcParc_gdv]', 0);
        if ($table) {
            $trs = $table->find('tr');
            $posicao  = 0;
            foreach ($trs as $tr) {
                if ($posicao > 0) {
                    $status = trim($tr->find('td', 2)->plaintext);
                    if ($status == 'Em parcelamento') {
                        $__EVENTTARGET = 'ctl00$contentPlaceH$wcParc$gdv';
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
                        $html =  $this->post2($post_fields);
                        $pedido = $this->obter_informacao_pedido($html);
                        $pedido['demonstrativo_pagamentos'] = $this->obter_demonstrativos($html);
                        $pedido['relacao_debitos_parcelas'] = $this->obter_relacao_debitos_parcelas($html, $pedido['numero']);

                        $pedidos[] = $pedido;
                    }else{
                        $numero_parcelamento_aux = trim($tr->find('td', 0)->plaintext);
                        $pedido = array();
                        $pedido['situacao'] = $status;
                        $pedido['numero'] = $numero_parcelamento_aux;

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
        $table = $html->find('table[id=ctl00_contentPlaceH_wcParc_gdv]', 0);
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
                $observacao = trim($tr->find('td')[4]->plaintext);

                $pedido_contribuinte = array(
                    'numero' => $numero,
                    'data_pedido' => $data_pedido,
                    'situacao' => $situacao,
                    'data_situacao' => $data_situacao,
                    'observacao' => $observacao,
                );
                break;
            }
            $i++;
        }
        $table = $html->find('table[id=ctl00_contentPlaceH_wcConsol_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;

        foreach ($trs as $tr) {
            if ($i > 0) {
                $valor_total_consolidado = trim($tr->find('td')[0]->plaintext);
                $quantidade_parcela = trim($tr->find('td')[1]->plaintext);
                $primeira_parcela = trim($tr->find('td')[2]->plaintext);
                $parcela_basica = trim($tr->find('td')[3]->plaintext);
                $data_consolidacao = trim($tr->find('td')[4]->plaintext);

                $pedido = array_merge($pedido_contribuinte, array(
                    'valor_total_consolidado' => $valor_total_consolidado,
                    'qtd_parcelas' => $quantidade_parcela,
                    'primeira_parcela' => $primeira_parcela,
                    'parcela_basica' => $parcela_basica,
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

        $table = $html->find('table[id=ctl00_contentPlaceH_wcPagto_gdv]', 0);
        $trs = $table->find('tr');
        $i = 0;
        $demonstrativos = array();
        foreach ($trs as $tr) {
            if ($i > 0) {
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

    private function obter_relacao_debitos_parcelas($page, $numero_parcelamento)
    {
        $html = new Simple_html_dom();
        $html->load($page);

        $__EVENTTARGET = 'ctl00$contentPlaceH$wcParc$gdv';
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

        $page = $this->post3($post_fields);
        // $cnpj = $this->obter_numero_documento();
        // $headers = array(
        //     "Connection: keep-alive",
        //     "Cache-Control: max-age=0",
        //     "Upgrade-Insecure-Requests: 1",
        //     "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36",
        //     "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
        //     "Sec-Fetch-Site: same-origin",
        //     "Sec-Fetch-Mode: navigate",
        //     "Sec-Fetch-User: ?1",
        //     "Sec-Fetch-Dest: iframe",
        //     'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
        //     "sec-ch-ua-mobile: ?0",
        //     'sec-ch-ua-platform: "Windows"',
        //     "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/ConsultaPedidoDetalhes.aspx",
        //     "Accept-Language: pt-BR,pt;q=0.9",
        //  );
        // $page = $this->exec("https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/Recibo.aspx?nroCnpj={$cnpj}&nroParcelamento={$numero_parcelamento}&nroConsolidacao=1&pagina=ConsultaPedidoDetalhes", $headers, '', false, $this->cookie_complemento);
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

    private function post($post_fields)
    {

        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx";

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
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

        $resp = $this->exec($url, $headers, $data, true, $this->cookie_complemento);

        return $resp;
    }

    private function post2($post_fields)
    {
        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/ConsultaPedido.aspx";

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
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

        $resp = $this->exec($url, $headers, $data, true, $this->cookie_complemento);
        return $resp;
    }

    private function post3($post_fields)
    {
        $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/ConsultaPedidoDetalhes.aspx";
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
            "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/ConsultaPedidoDetalhes.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true, $this->cookie_complemento);
        return $resp;
    }

    //BUSCAR E EMITIR PARECELAS
    function obter_simples_nacional_emissao_parcela()
    {
        // if(strlen($this->numero_documento_procuracao) < 12)
        //     return;
        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx');
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
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR . '123'
        );

        $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx";
        $page = $this->exec($url, array(""), http_build_query($post_fields), true);
        $html = $html->load($page);

        $div_principal = $html->find('div[id=ctl00_contentPlaceH_pnlParcelas]', 0);

        $parcelas = array();
        if ($div_principal) {
            $linhas = $div_principal->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            foreach ($linhas as $linha) {
                $valor = trim($linha->find('td', 1)->plaintext);
                $data_parcela = trim($linha->find('td', 0)->plaintext);
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

    //GERAR PDF
    function gerar_parcela_simplesnacional($data_emissao)
    {
        $resultado = $this->download_parcela_simplesnacional($data_emissao);
        if (!$resultado)
            return 'ERROECAC';
        return $resultado;
    }

    function download_parcela_simplesnacional($data_parcela)
    {
        $data_atual = date('Ymdhis');
        $tentativa = 5;

        while ($tentativa) {
            $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx";
            $headers = array(
                "Connection: keep-alive",
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
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

            $page = $this->exec($url, $headers, '', false, $this->cookie_complemento);
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
                "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/Default.aspx",
                "Accept-Language: pt-BR,pt;q=0.9",
            );
            $url = "https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx";
            $page = $this->exec($url, array(""), http_build_query($post_fields), true);
            $html = $html->load($page);

            $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/ReemitirDas.aspx";
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
                "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/Default.aspx",
                "Accept-Language: pt-BR,pt;q=0.9",
            );

            $__EVENTTARGET = 'ctl00$contentPlaceH$btnContinuar';
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

            $page = $this->exec($url, $headers, http_build_query($post_fields), true);
            $html = $html->load($page);

            $div_principal = $html->find('div[id=ctl00_contentPlaceH_pnlParcelas]', 0);
            $retorno = '';
            if ($div_principal) {
                $linhas = $div_principal->find('tr');
                if (count($linhas) == 0) {
                    echo 'vazio';
                }
                array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
                foreach ($linhas as $linha) {
                    if ($data_parcela ==  trim($linha->find('td', 0)->plaintext)) {
                        $caminho_local = $this->caminho_da_pasta_pdfs . "/DAS-Simples-Nacional-{$data_atual}.pdf";

                        $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/ReemitirDas.aspx";
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
                            "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/Default.aspx",
                            "Accept-Language: pt-BR,pt;q=0.9",
                        );

                        $__EVENTTARGET = str_replace('_', '$', trim($linha->find('a', 0)->id));
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

                        $content = $this->exec($url, $headers, http_build_query($post_fields), true);

                        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
                        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
                        if ($this->verifica_pdf_valido($content)) {
                            upload_google_source($content, $aux_dir_ext);
                            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
                        }
                    }
                }
            } else {
                echo 'Não há parcela disponível para reimpressão.';
            }

            if (!empty($retorno)){
                return $retorno;
            }
            $tentativa--;
            echo "<br>Tentariva: " . 5-$tentativa; 
        }

        return $retorno;
    }
}
