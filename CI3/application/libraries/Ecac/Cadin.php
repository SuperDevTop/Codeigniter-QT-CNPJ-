<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Cadin extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function baixar_pdf_cadin()
    {
        //DEFINE A DATA PARA GRAVAR NO ARQUIVO
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Y-m-d');
        $url = "https://sic.cav.receita.fazenda.gov.br/precadin-internet/api/contribuinterepresentado/relatoriodevedor/pdf?";
        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
           "sec-ch-ua-mobile: ?0",
           'sec-ch-ua-platform: "Windows"',
           "Upgrade-Insecure-Requests: 1",
           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
           "Sec-Fetch-Site: same-origin",
           "Sec-Fetch-Mode: navigate",
           "Sec-Fetch-User: ?1",
           "Sec-Fetch-Dest: iframe",
           "Referer: https://sic.cav.receita.fazenda.gov.br/precadin-internet/home.html",
           "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );
        $caminho_local = $this->caminho_da_pasta_pdfs."/cadin-".$data_atual."-{$this->numero_documento_procuracao}.pdf";
        return $this->obter_pdf($url, $caminho_local, $headers);
    }

//    public function baixar_pdf_cadin()
//    {
//        //DEFINE A DATA PARA GRAVAR NO ARQUIVO
//        date_default_timezone_set('America/Bahia');
//        $data_atual = date('Y-m-d');
//
//        $url = "https://sic.cav.receita.fazenda.gov.br/precadin-internet/api/contribuinterepresentado/relatoriodevedor/pdf?";
//
//        $curl = curl_init($url);
//        $this->setProxy($curl);
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//
//        $headers = array(
//            "Connection: keep-alive",
//            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
//            "sec-ch-ua-mobile: ?0",
//            'sec-ch-ua-platform: "Windows"',
//            "Upgrade-Insecure-Requests: 1",
//            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
//            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
//            "Sec-Fetch-Site: same-origin",
//            "Sec-Fetch-Mode: navigate",
//            "Sec-Fetch-User: ?1",
//            "Sec-Fetch-Dest: iframe",
//            "Referer: https://sic.cav.receita.fazenda.gov.br/precadin-internet/home.html",
//            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
//            "Cookie: COOKIECAV={$this->get_COOKIECAV()}; cw_siefpar=1.1; cw_siefpar2=1.1; cw_precadin=1.1",
//        );
//        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//        //for debug only!
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//
//        $fp = fopen ($this->caminho_da_pasta_pdfs."/cadin-".$data_atual."-{$this->numero_documento_procuracao}.pdf", 'w+');
//        curl_setopt($curl, CURLOPT_FILE, $fp);
//        curl_exec($curl);
//
//        if(curl_errno($curl))
//        {
//            echo curl_error($curl);
//            return false;
//        }
//        $caminho_local = $this->caminho_da_pasta_pdfs."/cadin-".$data_atual."-{$this->numero_documento_procuracao}.pdf";
//        echo $caminho_local;
//
////        $caminho_salvar = str_replace('C:\\xampp\\htdocs\\SistemaCrons\\ba\\', 'ba/', $caminho_local);
////        // $caminho_salvar_extra = str_replace('//', '/', $caminho_salvar);
////        upload_google($caminho_local, $caminho_salvar );
////        $a = array();
////        array_push($a, $caminho_local);
////        array_push($a, $caminho_salvar);
////        return $a;
//    }

}