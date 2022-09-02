<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class SituacaoFiscalCertificado extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function baixar_pdf_situacao_fiscal(){
//      Gerar essa configuração é necessaria porque ele faz um pre-processamento do pdf da situação fiscal, se chamar  a rotina de obter
//      o pdf sem fazer isso, o pdf vem vazio.
        $numero_documento = $this->obter_numero_documento();
        $numero_documento_certificado = $this->obter_numero_documento_certificado();

        $this->gera_configuracao_pdf("https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/GerenciaPedido/PedidoConsultaFiscal.asp?IndNovaConsulta=true&OpConsulta=1");
        $urlPF = "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=2&NIPesquisa={$numero_documento}&Ambiente=2&NICertificado={$numero_documento_certificado}&TipoNICertificado=1&SistemaChamador=0103";
        $urlPJ = "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=1&NIPesquisa={$numero_documento}&Ambiente=2&NICertificado={$numero_documento_certificado}&TipoNICertificado=2&SistemaChamador=0101";
        return $this->obter_pdf(false, strlen($this->numero_documento) > 11 ? $urlPJ : $urlPF);
    }

    private function gera_configuracao_pdf($url)
    {
        $this->gera_cookie();
        if ($url != ''){
            $this->url = $url;
        }

        curl_setopt($this->curl, CURLOPT_URL , $this->url );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
//      curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1' ,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36' ,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' ,
            'Sec-Fetch-Site: none' ,
            'Sec-Fetch-Mode: navigate' ,
            'Sec-Fetch-User: ?1' ,
            'Sec-Fetch-Dest: document' ,
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7' ,
        ));
        curl_setopt($this->curl,CURLOPT_ENCODING , "");
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);

        $response = curl_exec($this->curl);
        if(curl_errno($this->curl)){
            echo curl_error($this->curl);
        }
        return $this->converterCaracterEspecial($response);
    }

    private function obter_pdf($is_post = false, $url)
    {

        //DEFINE A DATA PARA GRAVAR NO ARQUIVO
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Y-m-d');
        $numero_documento = $this->obter_numero_documento_certificado();
        if ($url != ''){

            $this->url = $url;
        }
        $post_str = http_build_query([
            'ExibeCaptcha' => false, // campos fixos
            'id' => -1, // campos fixos
            'NI' => $this->numero_documento,
            'CodigoAcesso' => $this->codigo_acesso,
            'Senha' => $this->cerficado_senha,
            'ExibiuImagem' => true // campos fixos
        ]);
        $headers = array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection: keep-alive',
            'Host: www2.cav.receita.fazenda.gov.br',
            'Referer: https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/Relatorio/GerarRelatorio.asp',
            'Sec-Fetch-Dest: iframe',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-User: ?1',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36');
        curl_setopt($this->curl, CURLOPT_URL , $this->url );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        if($is_post) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_str);
        }
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "UZ_".uniqid());
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        $fp = fopen ($this->caminho_da_pasta_pdfs."/situação-fiscal-".$data_atual."-{$numero_documento}.pdf", 'w+');
        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl))
        {
            echo curl_error($this->curl);
            return false;
        }
        $caminho_local = $this->caminho_da_pasta_pdfs."/situação-fiscal-".$data_atual."-{$numero_documento}.pdf";
        $caminho_salvar = str_replace('C:\\xampp\\htdocs\\SistemaCrons\\ba\\', 'ba/', $caminho_local);
        // $caminho_salvar_extra = str_replace('//', '/', $caminho_salvar);
        upload_google($caminho_local, $caminho_salvar );
        $a = array();
        array_push($a, $caminho_local);
        array_push($a, $caminho_salvar);
        return $a;
    }

}