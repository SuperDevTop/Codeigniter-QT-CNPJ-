<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');



class SituacaoFiscalProcuracao extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function baixar_pdf_situacao_fiscal(){
        $numero_documento = $this->obter_numero_documento();
        $numero_documento_certificado = $this->obter_numero_documento_certificado();

        $url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/default.asp';
        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
           "sec-ch-ua-mobile: ?0",
           'sec-ch-ua-platform: "Windows"',
           "Upgrade-Insecure-Requests: 1",
           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
           "Sec-Fetch-Site: same-site",
           "Sec-Fetch-Mode: navigate",
           "Sec-Fetch-Dest: iframe",
           "Referer: https://cav.receita.fazenda.gov.br/",
           "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );
        $this->exec($url, $headers);

        $url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/IdentificaUsuario/index.asp';
        $headers = array(
            "Connection: keep-alive",
            "Content-Length: 0",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
           "sec-ch-ua-mobile: ?0",
           'sec-ch-ua-platform: "Windows"',
           "Upgrade-Insecure-Requests: 1",
           "Origin: https://www2.cav.receita.fazenda.gov.br",
           "Content-Type: application/x-www-form-urlencoded",
           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
           "Sec-Fetch-Site: same-origin",
           "Sec-Fetch-Mode: navigate",
           "Sec-Fetch-Dest: iframe",
           "Referer: https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/default.asp",
           "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );
        $this->exec($url, $headers, '', true);


        $url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/GerenciaPedido/PedidoConsultaFiscal.asp';
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Referer: https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/default.asp",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            );
        $this->exec($url, $headers);

        $url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/Relatorio/GeraRelatorioPdf.asp';
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
           "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );
        $this->exec($url, $headers);

        $data_atual = date('Y-m-d');
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
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36',
        );
        $filename=$this->caminho_da_pasta_pdfs."/situacao-fiscal-".$data_atual."-{$this->numero_documento_procuracao}.pdf";
        $urlPF = "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=2&NIPesquisa={$numero_documento}&Ambiente=2&NICertificado={$numero_documento_certificado}&TipoNICertificado=1&SistemaChamador=0103";
        $urlPJ = "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=1&NIPesquisa={$numero_documento}&Ambiente=2&NICertificado={$numero_documento_certificado}&TipoNICertificado=2&SistemaChamador=0101";
        $url = strlen($numero_documento) > 11 ? $urlPJ : $urlPF;
        return $this->obter_pdf($url, $filename, $headers);
    }
}