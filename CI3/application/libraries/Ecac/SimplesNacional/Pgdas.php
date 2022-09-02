<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Ecac/Ecac.php');


class Pgdas extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function obter_pgdas()
    {

        $url = "https://sinac.cav.receita.fazenda.gov.br/simplesnacional/aplicacoes/atspo/pgdasd2018.app/";
        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
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

        $this->exec($url, $headers);
        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/aplicacoes/atspo/pgdasd2018.app/",
            "Accept-Language: pt-BR,pt;q=0.9",

        );

        $pagina = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta', $headers);

        $html = new Simple_html_dom();
        $html->load($pagina);
        $tbDas = $html->find('table[class=table consulta]', 0);

        $resultado = array();
        $competencia = '';
        $dados = array();
        if ($tbDas) {
            $tbDasTr = $tbDas->find('tr');
            foreach ($tbDasTr as $tr) {
                if (isset($tr->style)) {
                    if ($tr->style == 'background-color:#DEF0C1') {
                        $competencia = $tr->plaintext;
                    }
                    $dados['compentencia'] = trim($competencia);
                    $dados['numero_das'] = '';
                    $dados['data_hora_emissao'] = '';
                    $dados['pago'] = '';
                    $dados['numero_declaracao'] = '';
                    $dados['data_hora_transmissao'] = '';
                    continue;
                }

                $texttr = $tr->plaintext;
                if (preg_match("/Declaração/",  $texttr)) {
                    $dados['numero_declaracao'] = trim($tr->find('td', 1)->plaintext);
                    $dados['data_hora_transmissao'] = trim($tr->find('td', 2)->plaintext);
                }

                if (preg_match("/DAS/",  $texttr)) {
                    $dados['numero_das'] = trim($tr->find('td', 7)->plaintext);
                    $dados['data_hora_emissao'] = trim($tr->find('td', 8)->plaintext);
                    $dados['pago'] = trim($tr->find('td', 10)->plaintext);
                    $resultado[] = $dados;
                }
            }
            foreach ($tbDasTr as $tr) {
                if (isset($tr->style)) {
                    if ($tr->style == 'background-color:#DEF0C1') {
                        $competencia = $tr->plaintext;
                    }
                    $dados['compentencia'] = trim($competencia);
                    $dados['numero_das'] = '';
                    $dados['data_hora_emissao'] = '';
                    $dados['pago'] = '';
                    $dados['numero_declaracao'] = '';
                    $dados['data_hora_transmissao'] = '';
                    continue;
                }

                $texttr = $tr->plaintext;
                if (preg_match("/Declaração/",  $texttr)) {
                    $dados['numero_declaracao'] = trim($tr->find('td', 1)->plaintext);
                    $dados['data_hora_transmissao'] = trim($tr->find('td', 2)->plaintext);
                    if ($this->nao_existe_declaracao($resultado, $dados['numero_declaracao']))
                        $resultado[] = $dados;
                }
            }
        }
        return $resultado;
    }

    function nao_existe_declaracao($resultado, $numero_declaracao)
    {
        foreach ($resultado as $r)
            if ($numero_declaracao == $r['numero_declaracao'])
                return false;
        return true;
    }

    /**
     * $path_script_simples_nacional_debitos
     *
     * CAMINHO PARA O SCRIPT simples nacional
     *
     * @var string
     */
    protected $path_script_simples_nacional_debitos     = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'simples_nacional_debitos.py';


    //Buscar debitos
    public function get_das_debitos()
    {
        echo $this->path_script_simples_nacional_debitos;
        die();
        $response = shell_exec("python3.9 {$this->path_script_simples_nacional_debitos}  \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\"");
        return json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }

    //gerar recibo
    public function obter_recibo($folder_pdf, $id_declaracao, $ano)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta/Recibo?idDeclaracao=' . $id_declaracao . '&ano=' . $ano;

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $content = $this->exec($url, $headers);

        $caminho_local = $folder_pdf . "PGDAS-RECIBO-{$data_atual}.pdf";

        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        } else $retorno = '';
        return $retorno;
    }

    //gerar declaração
    public function obter_declaracao($folder_pdf, $id_declaracao, $ano)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta/Declaracao?idDeclaracao=' . $id_declaracao . '&ano=' . $ano;

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $content = $this->exec($url, $headers);

        $caminho_local = $folder_pdf . "PGDAS-DECLARACAO-{$data_atual}.pdf";

        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        } else $retorno = '';

        return $retorno;
    }

    //extrato
    public function obter_extrato($folder_pdf, $id_declaracao, $numero_documento, $ano)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta/Extrato?idDeclaracao=' . $id_declaracao . '&numeroDocumento=' . $numero_documento . '&ano=' . $ano;

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta",
            "Accept-Language: pt-BR,pt;q=0.9",
        );

        $content = $this->exec($url, $headers);

        $caminho_local = $folder_pdf . "PGDAS-EXTRATO-{$data_atual}.pdf";

        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        } else $retorno = '';

        return $retorno;
    }

    public function obter_das($folder_pdf, $periodo, $outra_data = '')
    {
        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/PorPa';
        $page = $this->exec($url);
        $html = new Simple_html_dom();
        $html->load($page);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if (!empty($val) && !is_null($val))
                $vals[] = $val;
        }
        $__RequestVerificationToken = $vals[0];

        $data = http_build_query(array(
            'paDigitado' => $periodo,
            '__RequestVerificationToken' => $__RequestVerificationToken

        ));

        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Chromium";v="92", " Not A;Brand";v="99", "Google Chrome";v="92"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/PorPa",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );

        $page = $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/GerarDasPa', $headers, $data, true);
        $html = new Simple_html_dom();
        $html->load($page);

        $table_quota = $html->find("table", 9)->plaintext;
        if($table_quota)
        {
            $idDeclaracao = trim($html->find("input[id=hidIdDeclaracao]", 0)->value);

            // Verica se tem Das Prorrogacoes Quotas
            if (strpos($table_quota, 'QUOTA ÚNICA') !== false) {
                return $this->baixar_pdf_das_quotaunica($folder_pdf, $idDeclaracao, $outra_data);
            } else if (strpos($table_quota, 'Primeira Quota') !== false) {
                return $this->baixar_pdf_das_primeiraquota($folder_pdf, $idDeclaracao, $outra_data);
            } else {
                if (!empty($outra_data)) {
                    $outra_data = $this->apenas_numero($outra_data);
                    $url = "https://sinac.cav.receita.fazenda.gov.br//SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/OutraData/?idDeclaracao={$idDeclaracao}&outraData={$outra_data}";
                    $this->exec($url);
                }

                $dataValidade = $html->find("input[id=hidDataValidade]", 0)->value;

                return $this->baixar_pdf_das($folder_pdf, $idDeclaracao, $dataValidade, $__RequestVerificationToken);
            } 
        }  

        return '';  
       
    }

    function baixar_pdf_das($folder_pdf, $idDeclaracao, $dataValidade, $__RequestVerificationToken)
    {
        $post_str = http_build_query(
            array(
                'dataValidade' => $dataValidade,
                '__RequestVerificationToken' => $__RequestVerificationToken
            )
        );
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Origin: https://sinac.cav.receita.fazenda.gov.br",
            "Content-Type: application/x-www-form-urlencoded",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/GerarDasPa",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );
        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/GerarDas';

        $caminho_local = $folder_pdf . "/PGDASD-DAS-{$idDeclaracao}.pdf";

        $content = $this->exec($url, $headers, $post_str, true);

        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        $retorno ='';
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        }
        return $retorno;
    }

    function baixar_pdf_das_quotaunica($folder_pdf, $idDeclaracao, $outra_data = '')
    {
        if (!empty($outra_data)) {
            $outra_data = $this->apenas_numero($outra_data);
            $url = "https://sinac.cav.receita.fazenda.gov.br//SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas/OutraData/?idDeclaracao={$idDeclaracao}&outraData={$outra_data}&qualDas=0";
            $headers = array(
                "Connection: keep-alive",
                'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
                "sec-ch-ua-mobile: ?0",
                'sec-ch-ua-platform: "Windows"',
                "Upgrade-Insecure-Requests: 1",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                "Sec-Fetch-Site: same-origin",
                "Sec-Fetch-Mode: navigate",
                "Sec-Fetch-User: ?1",
                "Sec-Fetch-Dest: document",
                "Referer: https://sinac.cav.receita.fazenda.gov.br//SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas/OutraData/?idDeclaracao={$idDeclaracao}&outraData={$outra_data}&qualDas=0",
                "Accept-Language: pt-BR,pt;q=0.9",
            );
            $page = $this->exec($url, $headers);
            $html = new Simple_html_dom();
            $html->load($page);
            $span_error = $html->find("span[class=errorMsg]", 0);

            // verificar se deu erro pra consolidar outra data, se deu ele recarrega a pagina principal
            if ($span_error && strpos($span_error->plaintext, 'Não é possível reconsolidar para esta data') !== false) {
                $headers = array(
                    "Connection: keep-alive",
                    "Cache-Control: max-age=0",
                    "Upgrade-Insecure-Requests: 1",
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                    "Sec-Fetch-Site: same-origin",
                    "Sec-Fetch-Mode: navigate",
                    "Sec-Fetch-User: ?1",
                    "Sec-Fetch-Dest: document",
                    'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
                    "sec-ch-ua-mobile: ?0",
                    'sec-ch-ua-platform: "Windows"',
                    "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/PorPa",
                    "Accept-Language: pt-BR,pt;q=0.9",

                );
                $this->exec('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas?idDeclaracao={$idDeclaracao}', $headers);
            }
        }

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas?idDeclaracao={$idDeclaracao}",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        $caminho_local = $folder_pdf . "/PGDASD-DAS-{$idDeclaracao}.pdf";

        $content = $this->exec($url, $headers);

        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        }
        return $retorno;
    }

    function baixar_pdf_das_primeiraquota($folder_pdf, $idDeclaracao, $outra_data = '')
    {
        if (!empty($outra_data)) {
            $outra_data = $this->apenas_numero($outra_data);
            $url = "https://sinac.cav.receita.fazenda.gov.br//SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas/OutraData/?idDeclaracao={$idDeclaracao}&outraData={$outra_data}&qualDas=1";
            $headers = array(
                "Connection: keep-alive",
                'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
                "sec-ch-ua-mobile: ?0",
                'sec-ch-ua-platform: "Windows"',
                "Upgrade-Insecure-Requests: 1",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                "Sec-Fetch-Site: same-origin",
                "Sec-Fetch-Mode: navigate",
                "Sec-Fetch-User: ?1",
                "Sec-Fetch-Dest: document",
                "Referer: https://sinac.cav.receita.fazenda.gov.br//SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas/OutraData/?idDeclaracao={$idDeclaracao}&outraData={$outra_data}&qualDas=1",
                "Accept-Language: pt-BR,pt;q=0.9",
            );
            $page = $this->exec($url, $headers);
            $html = new Simple_html_dom();
            $html->load($page);
            $span_error = $html->find("span[class=errorMsg]", 0);

            // verificar se deu erro pra consolidar outra data, se deu ele recarrega a pagina principal
            if ($span_error && strpos($span_error->plaintext, 'Não é possível reconsolidar para esta data') !== false) {
                $headers = array(
                    "Connection: keep-alive",
                    "Cache-Control: max-age=0",
                    "Upgrade-Insecure-Requests: 1",
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                    "Sec-Fetch-Site: same-origin",
                    "Sec-Fetch-Mode: navigate",
                    "Sec-Fetch-User: ?1",
                    "Sec-Fetch-Dest: document",
                    'sec-ch-ua: "Google Chrome";v="93", " Not;A Brand";v="99", "Chromium";v="93"',
                    "sec-ch-ua-mobile: ?0",
                    'sec-ch-ua-platform: "Windows"',
                    "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Das/PorPa",
                    "Accept-Language: pt-BR,pt;q=0.9",

                );
                $this->exec("https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas?idDeclaracao={$idDeclaracao}", $headers);
            }
        }

        $url = 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas/GerarDasPrimeiraQuota';

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96""',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/DasProrrogacoesQuotas?idDeclaracao={$idDeclaracao}",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        $caminho_local = $folder_pdf . "/PGDASD-DAS-{$idDeclaracao}.pdf";

        $content = $this->exec($url, $headers);
        //echo $content; die();
        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ($this->verifica_pdf_valido($content)) {
            upload_google_source($content, $aux_dir_ext);
            $retorno = "https://storage.googleapis.com/cron-veri-files-br/" . $aux_dir_ext;
        }
        return $retorno;
    }

    function verifica_pdf_valido($content)
    {
        if (preg_match("/^%PDF-1./", $content)) {
            return true;
        } else {
            return false;
        }
    }
}
