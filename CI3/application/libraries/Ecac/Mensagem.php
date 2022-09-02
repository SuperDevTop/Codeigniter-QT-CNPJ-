<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Mensagem extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    function obter_mensagem_caixa_postal(){

        $url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/ListarMensagemAction.aspx';
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
           "Sec-Fetch-Dest: iframe",
           "Referer: https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=00006",
           "Accept-Language: pt-BR,pt;q=0.9",
        );
        $page = $this->exec($url, $headers);
        $html = new Simple_html_dom();
        $html->load($page);

        try {

            $lidas = $html->find('span[id=lbMensagensLidas]',0)->plaintext;
            $nao_lidas = $html->find('span[id=lbMensagensNaoLidas]',0)->plaintext;

            $lidas = preg_replace("/[^0-9]/", "", $lidas);
            $nao_lidas = preg_replace("/[^0-9]/", "", $nao_lidas);
            $array_mensagens = array(
                'lidas' => $lidas,
                'nao_lidas' => $nao_lidas,
                'data' => date('Y-m-d'),
                'mensagens' => array()
            );

            foreach($html->find('tr[style="color:Black;background-color:#EEEEEE;"]') as $e){
                $url_assunto = str_replace( "&amp;", "&", $e->find('td a')[3]->href);
                $url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/' . trim($url_assunto);

                $assunto = "";
                $codigo_receita_ecac = str_replace( "&amp;", "&", $e->find('td a')[2]->href);

                $objeto_importante = $e->find('td a')[0];
                $importante = $objeto_importante->find('img');
                $importante_text = '0';

                foreach($importante as $span){
                    $importante_text = '1';
                }

                $objeto_lida =  $e->find('td a')[1];
                $lida = $objeto_lida->find('img');
                $lida_text = '0';
                $id_mensagem = "";

                foreach($lida as $img){
                    if($img->title == "Mensagem lida"){
                        $lida_text = '1';
                        $page = $this->exec($url, $headers);
                        $htmlConteudoMsg = new Simple_html_dom($page);
                        $assunto = $htmlConteudoMsg->find('span[id=msgConteudo]',0)->plaintext;
                    }else{
                        $lida_text = '0';
                        $id_img_aux = $img->id;
                        $array_id = explode("_", $id_img_aux);
                        $id_mensagem = $array_id[3];
                    }
                }

                $lida = $e->find('td')[2]->plaintext;
                $remetente = $e->find('td')[3]->plaintext;
                $mensagem_assunto = $e->find('td')[4]->plaintext;
                $recebida_em = $e->find('td')[5]->plaintext;

                $array_mensagens['mensagens'][] = array(
                    'assunto' => $mensagem_assunto,
                    'conteudo' => $assunto,
                    'remetente' => $remetente,
                    'recebida_em' => $recebida_em,
                    'lida' => $lida_text,
                    'importante' => $importante_text,
                    'id_mensagem' => $id_mensagem,
                    'codigo_receita_ecac' => $codigo_receita_ecac
                );
            }

            foreach($html->find('tr[style="color:Black;background-color:Gainsboro;"]') as $e){
                $url_assunto = str_replace( "&amp;", "&", $e->find('td a')[3]->href);
                $url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/' . trim($url_assunto);

                $assunto = "";
                $codigo_receita_ecac = str_replace( "&amp;", "&", $e->find('td a')[2]->href);
                
                $objeto_importante = $e->find('td a')[0];
                $importante = $objeto_importante->find('img');
                $importante_text = '0';

                foreach($importante as $span){
                    $importante_text = '1';
                }

                $objeto_lida =  $e->find('td a')[1];
                $lida = $objeto_lida->find('img');
                $lida_text = '0';
                $id_mensagem = "";

                foreach($lida as $img){
                    if($img->title == "Mensagem lida"){
                        $lida_text = '1';
                        $page = $this->exec($url, $headers);
                        $htmlConteudoMsg = new Simple_html_dom($page);
                        $assunto = $htmlConteudoMsg->find('span[id=msgConteudo]',0)->plaintext;
                    }else{
                        $lida_text = '0';
                        $id_img_aux = $img->id;
                        $array_id = explode("_", $id_img_aux);
                        $id_mensagem = $array_id[3];
                    }
                }

                $lida = $e->find('td')[2]->plaintext;
                $remetente = $e->find('td')[3]->plaintext;
                $mensagem_assunto = $e->find('td')[4]->plaintext;
                $recebida_em = $e->find('td')[5]->plaintext;

                $array_mensagens['mensagens'][] = array(
                    'assunto' => $mensagem_assunto,
                    'conteudo' => $assunto,
                    'remetente' => $remetente,
                    'recebida_em' => $recebida_em,
                    'lida' => $lida_text,
                    'importante' => $importante_text,
                    'id_mensagem' => $id_mensagem,
                    'codigo_receita_ecac' => $codigo_receita_ecac
                );
            }

            return $array_mensagens;

        } catch (Exception $er) {

            return false;
        }


    }
}