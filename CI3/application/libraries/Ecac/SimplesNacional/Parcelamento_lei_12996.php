<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Ecac/Ecac.php');

class Parcelamento_lei_12996 extends Ecac
{
    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    function extrato_e_demonstrativos($cnpj)
    {
        $demonstrativos = array();

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: empty",
            "Referer: https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/public/css/lei11941.css",
            "Accept-Language: pt-BR,pt;q=0.9",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With: XMLHttpRequest",
            "Origin: https://www4.cav.receita.fazenda.gov.br",
        );

        $page = $this->exec('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/MenuLei12996.aspx', $headers);
        $html = new Simple_html_dom();
        $html->load($page);

        $divNaoOptante = $html->find('div[id=divNaoOptante]', 0)->plaintext;
        if ($divNaoOptante == 1) {
            echo '<br>achou modal que indica não possuir parcela no cnpj:' . $cnpj;
            return '';
        }

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: iframe",
            "Referer: https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/MenuLei12996.aspx",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        $page = $this->exec('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Demonstrativo/Demonstrativo.aspx', $headers);
        $html = new Simple_html_dom();
        $html->load($page);  

        try {
            $cnpj_formatado = $html->find('label[id=lblNi]', 0)->plaintext;
            $nome_empresarial = $html->find('label[id=lblNiNome]', 0)->plaintext;

            $headers = array(
                "Connection: keep-alive",
                'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
                "Accept: application/json, text/javascript, */*; q=0.01",
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                "X-Requested-With: XMLHttpRequest",
                "sec-ch-ua-mobile: ?0",
                'sec-ch-ua-platform: "Windows"',
                "Origin: https://www4.cav.receita.fazenda.gov.br",
                "Sec-Fetch-Site: same-origin",
                "Sec-Fetch-Mode: cors",
                "Sec-Fetch-Dest: empty",
                "Referer: https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Demonstrativo/Demonstrativo.aspx",
                "Accept-Language: pt-BR,pt;q=0.9",
            );
            $page = $this->exec('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Ajaj.aspx', $headers, http_build_query(['act' => 'InfoNiModalidades', 'dt' => '{"TipoNi":"1","Ni":"' . $cnpj . '"}']), true);
            $html = new Simple_html_dom();
            $html->load($page);

            $response = json_decode($html);

            $municipio = $response->Contribuinte->NomeMunicipio;

            foreach ($response->Modalidades as $modalidade) {
                if ($modalidade->NomeSituacao == 'Em Parcelamento' && preg_match("/Lei 12.996/i", $modalidade->NomeModalidade)) {

                    $demonstrativo = array(
                        'cnpj_formatado' => $cnpj_formatado,
                        'nome_empresarial' => $nome_empresarial,
                        'municipio' => $municipio,
                        'data_adesao' => $modalidade->DataAdesao,
                        'data_validacao' => $modalidade->DataValidacao,
                        'data_negociacao' => $modalidade->DataNegociacao,
                        'data_efeito_exclusao' => $modalidade->DataEfeitoExclusao,
                        'data_ciencia' => $modalidade->DataCiencia,
                        'data_encerramento' => $modalidade->DataEncerramento,
                        'data_liquidacao_divida' => $modalidade->DataLiquidacaoDivida,
                        'data_exclusao' => $modalidade->DataExclusao,
                        'codigo_motivo_exclusao' => $modalidade->CodigoMotivoExclusao,
                        'in_solicitacao_reativacao' => $modalidade->InSolicitacaoReativacao,
                        'cod_fase' => $modalidade->CodFase,
                        'cod_modalidade' => $modalidade->CodModalidade,
                        'cod_situacao' => $modalidade->CodSituacao,
                        'nome_modalidade' => $modalidade->NomeModalidade,
                        'nome_situacao' => $modalidade->NomeSituacao,
                        'proximo_dia_util' => $response->Contribuinte->DataUltDiaUtil,
                    );

                    $headers = array(
                        "Connection: keep-alive",
                        'sec-ch-ua: "Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
                        "Accept: application/json, text/javascript, */*; q=0.01",
                        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                        "X-Requested-With: XMLHttpRequest",
                        "sec-ch-ua-mobile: ?0",
                        'sec-ch-ua-platform: "Windows"',
                        "Origin: https://www4.cav.receita.fazenda.gov.br",
                        "Sec-Fetch-Site: same-origin",
                        "Sec-Fetch-Mode: cors",
                        "Sec-Fetch-Dest: empty",
                        "Referer: https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Demonstrativo/Demonstrativo.aspx",
                        "Accept-Language: pt-BR,pt;q=0.9",
                    );
                    $page = $this->exec('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/AjajNegociacaoLei12996.aspx', $headers, http_build_query(['act' => 'DemonstrativoPrestacoes',  'dt' => '{"Tipo":"1","Ni":"' . $cnpj . '","Parcelamento":"' . $modalidade->CodModalidade . '","Msg":"","Operacao":""}']), true);
                    $html = new Simple_html_dom();
                    $html->load($page);

                    $prestacoes = json_decode($html);

                    $demonstrativo['cod_receita'] = $prestacoes->Divida->Codigo_receita;
                    
                    $parcelas = array();
                    foreach ($prestacoes->Parcelas as $parcela) {
                        if (!empty($parcela->Indicador_Parcela_Devida)) {
                            $parcelas[] = array(
                                'parcela_id' => $parcela->Parcela_Id,
                                'data_parcela' => $parcela->Data_Parcela,
                                'valor_parc_minima' => $parcela->Valor_Parc_Minima,
                                'valor_parcela_divida' => $parcela->Valor_Parcela_Divida,
                                'valor_parc_calculada' => $parcela->Valor_Parc_Calculada,
                                'saldo_parc_devedora' => $parcela->Saldo_Parc_Devedora,
                                'juros_parc_deverdora' => $parcela->Juros_Parc_Deverdora,
                                'indicador_parcela_devida' => $parcela->Indicador_Parcela_Devida,
                                'indicador_situacao_parcela' => $parcela->Indicador_Situacao_Parcela,
                                'indicador_reducao' => $parcela->Indicador_Reducao,
                                'valor_total_arrecadacao' => $parcela->Valor_Total_Arrecadacao,
                                'valor_reducao_mes' => $parcela->Valor_Reducao_Mes,
                                'valor_antecipacao_mes' => $parcela->Valor_Antecipacao_Mes,
                                'quantidade_parc_red' => $parcela->Quantidade_Parc_Red,
                            );
                        }
                    }
                    $demonstrativo['parcelas'] = $parcelas;
                    $demonstrativos[] = $demonstrativo;
                }
            }
            return $demonstrativos;
        } catch (Exception $e) {
            return '';
        }
    }
}
