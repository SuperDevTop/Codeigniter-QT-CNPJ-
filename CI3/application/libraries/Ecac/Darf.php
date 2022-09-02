<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Darf extends Ecac
{
    private $cookie_complemento = 'cw_aplicacao=1.8; cw_sisobrapref=1.8';

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    function busca_darfs($darfs_existentes, $cnpj)
    {
        $page = $this->exec('https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/Default.aspx', array(''), '', false, $this->cookie_complemento);
        $html = new Simple_html_dom();
        $html->load($page);
        
        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            $vals[] = $val;
        }

        $__EVENTTARGET = $vals[0];
        $__EVENTARGUMENT = $vals[1];
        $__LASTFOCUS = $vals[2];

        $__VIEWSTATE = $vals[3];
        $__VIEWSTATEGENERATOR = $vals[4];
        $__EVENTVALIDATION = $vals[5];

        $post_fields = array(
            '__EVENTTARGET' => $__EVENTTARGET,
            '__EVENTARGUMENT' => $__EVENTARGUMENT,
            '__LASTFOCUS' => $__LASTFOCUS,
            '__VIEWSTATE' => $__VIEWSTATE,
            '__EVENTVALIDATION' => $__EVENTVALIDATION,
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
            'campoTipoDocumento'=> 'Todos',
            'campoDataArrecadacaoInicial'=> '01/01/2021' ,
            'campoDataArrecadacaoFinal'=> '', 
            'campoCodReceita'=> '',
            'campoNumeroDocumento'=> '', 
            'campoValorInicial'=> '',
            'campoValorFinal'=> '',
            'botaoConsultar'=> 'Consultar'
        );

        $page = $this->post($post_fields);

        $html = new Simple_html_dom();
        $html->load($page);
        $darfs = array();

        
        $table = $html->find('table[id=listagemDARF]', 0);
        if ($table) {
            $trs = $table->find('tr');
            $posicao = 0;
            foreach ($trs as $tr) {

                if ($posicao > 0) {

                    //verifica se o darf ja foi cadastrado no banco
                    $numero_documento_darf = trim($tr->find('td', 3)->plaintext);
                    if(isset($darfs_existentes[$numero_documento_darf])){
                        echo "entrou";
                        continue;
                    }

                    $tipo_documento = trim($tr->find('td', 2)->plaintext);
                    $periodo_apuracao = trim($tr->find('td', 5)->plaintext);
                    $periodo_arrecadacao = trim($tr->find('td', 6)->plaintext);
                    $data_vencimento = trim($tr->find('td', 7)->plaintext);
                    $codigo_receita = trim($tr->find('td', 8)->plaintext);
                    $numero_referencia = trim($tr->find('td', 9)->plaintext);
                    $valor_total = trim($tr->find('td', 10)->plaintext);

                    //busca  comprovante de pagamento
                    $__EVENTTARGET = trim($tr->find('input', 1)->name);

                    $nodes = $html->find("input[type=hidden]");
                    $vals = array();
                    foreach ($nodes as $node) {
                        $val = $node->value;
                        $vals[] = $val;
                           
                    }

                    $__EVENTARGUMENT = $vals[1];
                    $__LASTFOCUS = $vals[2];

                    $__VIEWSTATE = $vals[3];
                    $__VIEWSTATEGENERATOR = $vals[4];
                    $__EVENTVALIDATION = $vals[5];

                    $post_fields = array(
                        '__EVENTTARGET' => $__EVENTTARGET,
                        '__EVENTARGUMENT' => '',
                        '__LASTFOCUS'=> '',
                        '__VIEWSTATE' => $__VIEWSTATE,
                        '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                        '__EVENTVALIDATION' => $__EVENTVALIDATION
                    );

                    $link_pdf = '';

                    // echo $link_pdf;
                    // die();
                    //fim da busca pelo comprovante de pagamento

                    $item_darf = array(
                        'cnpj' => $cnpj,
                        'numero_documento' => $numero_documento_darf,
                        'tipo_documento' => $tipo_documento,
                        'periodo_apuracao' => $periodo_apuracao,
                        'periodo_arrecadacao' => $periodo_arrecadacao,
                        'data_vencimento' => $data_vencimento,
                        'codigo_receita' => $codigo_receita,
                        'numero_referencia' => $numero_referencia,
                        'valor_total' => $valor_total,
                        'path_download' => $link_pdf
                    );

                    $darfs[] = $item_darf;
                }
                $posicao++;
            }
        }

        return $darfs;
    }

    private function post($post_fields)
    {

        $url = "https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/Default.aspx";

        $headers = array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Language' => 'pt-BR,pt;q=0.9',
            'Cache-Control' => 'max-age=0',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Origin' => 'https://cav.receita.fazenda.gov.br',
            'Referer' => 'https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/Default.aspx',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
            'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="102", "Google Chrome";v="102"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'Accept-Encoding' => 'gzip'
        );

        $data = http_build_query($post_fields);

        $resp = $this->exec($url, $headers, $data, true);

        return $resp;
    }

    function download_comprovante($postfields, $numero_documento_darf){
        $data_atual = date('Ymdhis');

        // $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $caminho_local = $this->caminho_da_pasta_pdfs . "/DARF-{$numero_documento_darf}.pdf";

        $url = "https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/PagtoWebList.aspx";
        $headers = array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Language' => 'pt-BR,pt;q=0.9',
            'Cache-Control' => 'max-age=0',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Origin' => 'https://cav.receita.fazenda.gov.br',
            'Referer' => 'https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/PagtoWebList.aspx',
            'Sec-Fetch-Dest' => 'iframe',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
            'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="102", "Google Chrome";v="102"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'Accept-Encoding' => 'gzip',
        );

        $content = $this->exec($url, $headers, http_build_query($postfields), true);

        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        }else{
            //erro
            $retorno = "";
        }

        return $retorno;

    }
    
}