<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Simple_html_dom.php');
require_once(APPPATH.'libraries/Certificate/Pkcs12.php');

class Ecac_robo_library_procuracao {

    /**
     * LOGIN URL
     *
     * URL que faz o login com codigo de acesso
     *
     * @var	string
     */
    protected $login_url		= 'https://cav.receita.fazenda.gov.br//autenticacao/Login/CodigoAcesso';

    /**
     * LOGIN URL
     *
     * URL que faz o login com certificado digital A1
     *
     * @var	string
     */
    protected $login_url_certificado = 'https://certificado.sso.acesso.gov.br/authorize?response_type=code&client_id=cav.receita.fazenda.gov.br&scope=openid+govbr_recupera_certificadox509+govbr_confiabilidades&redirect_uri=https%3A%2F%2Fcav.receita.fazenda.gov.br%2Fautenticacao%2Flogin%2Fgovbrsso';

    /**
     * CODIGO ACESSO
     *
     * Codigo de acesso pessoal do ecac, usado somente
     * se quiser acessar o portal sem o certificado com o codigo de acesso
     *
     * @var	string
     */
    protected $codigo_acesso		= '';

    /**
     * Numero do documento procuracao
     *
     * Número do documento CPF ou CNPJ
     *
     * @var	string
     */
    protected $numero_documento_procuracao		= '';

    /**
     * Numero do documento certificado
     *
     * Número do documento CPF ou CNPJ
     *
     * @var	string
     */
    protected $numero_documento_certificado		= '';

    /**
     * SENHA
     *
     * Senha para o acesso através de codigo de acesso
     *
     * @var	string
     */
    protected $senha_codigo_acesso		= '';

    /**
     * PRIVATE_KEY
     *
     * Path para a chave privada do certificado
     *
     * @var	string
     */
    protected $private_key		= '';

    /**
     * PUBLIC_KEY
     *
     * Path para chave publica do certificado
     *
     * @var	string
     */
    protected $public_key		= '';

    /**
     * CERT_KEY
     *
     * Path para o arquivo cert key
     *
     * @var	string
     */
    protected $cert_key		= '';

    /**
     * CERTIFICADO_SENHA
     *
     * Senha do certificado digital
     *
     * @var	string
     */
    protected $cerficado_senha		= '';

    /**
     * caminho_certificado
     *
     * caminho do certificado digital
     *
     * @var	string
     */
    protected $caminho_certificado		= '';

    /**
     * URL
     *
     * A url que deseja acessar no momento após o login
     *
     * @var	string
     */
    protected $url		= '';

    /**
     * CAMINHO_DA_PASTA_PDFS
     *
     * Indica o caminho para a pasta que salva os pdfs
     *
     * @var	string
     */
    protected $caminho_da_pasta_pdfs		= '';

    /**
     * ACESSO_VALIDO
     *
     * Valida a conexão feita com o site, caso tenha dado algum erro emite uma mensagem e vai para o próximo
     *
     * @var	string
     */
    protected $acesso_valido		= true;

    /**
     * CI Singleton
     *
     * @var	object
     */
    protected $CI;

    private $curl;

    public function __construct($params = array())
    {
        $this->CI =& get_instance();
        $this->curl = curl_init();
        $this->initialize($params);
        $this->gerar_chaves();
        if(!$this->conectar_via_certificado())
            $this->acesso_valido = false;


        log_message('info', 'Ecac Robo Class Initialized');
    }

    public function initialize(array $params = array())
    {
        foreach ($params as $key => $val)
        {
            if (property_exists($this, $key))
            {
                $this->$key = $val;
            }
        }

        return $this;
    }

    /**
     * gerar_chaves
     *
     * Gera as chaves de acesso do certificado informado
     *
     */

    function gerar_chaves(){
//		Gera a cadeia de cerficados do ecac
        $aCerts[] = APPPATH . 'libraries/Certificate/cadeia_certificados_receita/acrfbv3.cer';
        $aCerts[] = APPPATH . 'libraries/Certificate/cadeia_certificados_receita/acserprorfbv3.cer';
        $aCerts[] = APPPATH . 'libraries/Certificate/cadeia_certificados_receita/icpbrasilv2.cer';

        $pkcs = new Pkcs12(APPPATH . 'libraries/Certificate/certificados_clientes/');

        $pkcs->loadPfxFile($this->caminho_certificado, $this->cerficado_senha);
//		adiciona a cadeia ao certificado
        $pkcs->aadChain($aCerts);

//		seta as chaves na classe
        $this->public_key = $pkcs->pubKeyFile;
        $this->private_key = $pkcs->priKeyFile;
        $this->cert_key = $pkcs->certKeyFile;
        $this->numero_documento_certificado = $pkcs->cnpj;
        return true;
    }
    /**
     * conectar_via_certificado
     *
     * Abre a conecção com o portal do o ecac.
     * Obrigatorio informar caminho_cerficado e cerficado_senha
     *
     *
     */
    function conectar_via_certificado(){
        $url_login = $this->login_url_certificado ;

// 		Faz login e pega o cookie de sessao
        curl_setopt($this->curl, CURLOPT_URL , $url_login );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "UZ_".uniqid());
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Pragma: no-cache", "Cache-Control: no-cache",'Content-type: text/html; charset=UTF-8'));
        curl_setopt($this->curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_SSLCERT, $this->public_key);
        curl_setopt($this->curl, CURLOPT_SSLKEY, $this->private_key);
        curl_setopt($this->curl, CURLOPT_CAINFO, $this->cert_key);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_PORT , 443);

        $response = curl_exec( $this->curl );

        $page = $this->obter_pagina(false, 'https://cav.receita.fazenda.gov.br/autenticacao/Login');
        $html = new Simple_html_dom();
        $html->load($page);
        $href = $html->find('div[id=login-dados-certificado]',0)->find('a', 0)->href;
        $response = $this->obter_pagina(false, $href);
        $acesso_valido = $this->validar_acesso($this->converterCaracterEspecial($response));

        return $acesso_valido;
    }

    public


    function obter_mensagem_caixa_postal(){

        $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/ListarMensagemAction.aspx';
        $page = $this->obter_pagina(false);
        $html = new Simple_html_dom();
        $html->load($page);
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
            $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/' . trim($url_assunto);

            $assunto = "";

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
                    $page = $this->obter_pagina(false);
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
                'id_mensagem' => $id_mensagem
            );
        }

        foreach($html->find('tr[style="color:Black;background-color:Gainsboro;"]') as $e){
            $url_assunto = str_replace( "&amp;", "&", $e->find('td a')[3]->href);
            $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/' . trim($url_assunto);

            $assunto = "";

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
                    $page = $this->obter_pagina(false);
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
                'id_mensagem' => $id_mensagem
            );
        }

        return $array_mensagens;
    }

    public function buscar_conteudo_mensagem($id_msg){
        $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/ListarMensagemAction.aspx';
        $page = $this->obter_pagina(false);
        $html = new Simple_html_dom();
        $html->load($page);

        $mensagem_conteudo = "";

        foreach($html->find('tr[style="color:Black;background-color:#EEEEEE;"]') as $e){
            $url_assunto = str_replace( "&amp;", "&", $e->find('td a')[3]->href);
            $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/' . trim($url_assunto);

            $objeto_lida =  $e->find('td a')[1];
            $lida = $objeto_lida->find('img');
            $id_mensagem = "";

            foreach($lida as $img){

                $id_img_aux = $img->id;
                $array_id = explode("_", $id_img_aux);
                $id_mensagem = $array_id[3];

                if($id_mensagem == $id_msg){
                    $page = $this->obter_pagina(false);
                    $htmlConteudoMsg = new Simple_html_dom($page);
                    $mensagem_conteudo = $htmlConteudoMsg->find('span[id=msgConteudo]',0)->plaintext;
                    break;
                }
            }

            if($mensagem_conteudo != ""){
                break;
            }
        }

        foreach($html->find('tr[style="color:Black;background-color:Gainsboro;"]') as $e){
            $url_assunto = str_replace( "&amp;", "&", $e->find('td a')[3]->href);
            $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/' . trim($url_assunto);

            $objeto_lida =  $e->find('td a')[1];
            $lida = $objeto_lida->find('img');
            $id_mensagem = "";

            foreach($lida as $img){

                $id_img_aux = $img->id;
                $array_id = explode("_", $id_img_aux);
                $id_mensagem = $array_id[3];

                if($id_mensagem == $id_msg){
                    $page = $this->obter_pagina(false);
                    $htmlConteudoMsg = new Simple_html_dom($page);
                    $mensagem_conteudo = $htmlConteudoMsg->find('span[id=msgConteudo]',0)->plaintext;
                    break;
                }
            }

            if($mensagem_conteudo != ""){
                break;
            }
        }

        return $mensagem_conteudo;
    }

    public function obter_situacao_fiscal(){

        $this->url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/GerenciaPedido/DiagnosticoFiscal.asp?IndDiagFiscal=1';
        $page = $this->obter_pagina(false);
        $html = new Simple_html_dom();
        $html->load($page);
        $situacao_fiscal = true;

        $texto = $html->find('table', 0)->plaintext;
// tem algumas paginas que o texto esta errado e vindo "No foram detectadas" ai tive que fazer assim

        if(strpos($texto, 'Não foram detectadas irregularidades') !== false || strpos($texto, 'No foram detectadas irregularidades') !== false)
            $situacao_fiscal = false;
        return $situacao_fiscal;
    }

    public function obter_pagina($is_post = true, $url = '', $post_str = [], $headers = [])
    {
        if ($url != ''){
            $this->url = $url;
        }

        if(count($post_str) == 0)
            $post_str = http_build_query([
                'ExibeCaptcha' => false, // campos fixos
                'id' => -1, // campos fixos
                'NI' => $this->numero_documento_certificado,
                'CodigoAcesso' => $this->codigo_acesso,
                'Senha' => $this->senha_codigo_acesso,
                'ExibiuImagem' => true // campos fixos
            ]);

        if( count( $headers ) == 0 )
            $headers = array("Pragma: no-cache", "Cache-Control: no-cache", 'Content-type: text/html; charset=UTF-8');

        curl_setopt($this->curl, CURLOPT_URL , $this->url );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        if($is_post) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_str);
        }
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($this->curl);
        $httpcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if(curl_errno($this->curl)){
            echo curl_error($this->curl);
        }
        return $this->converterCaracterEspecial($response);
    }

    public function gera_configuracao_pdf($url)
    {
        $this->gera_cookie();
        if ($url != ''){
            $this->url = $url;
        }

        curl_setopt($this->curl, CURLOPT_URL , $this->url );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, true);
//		curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
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
            'Referer: https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/default.asp',
            'Sec-Fetch-Dest: document' ,
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7' ,
        ));
        curl_setopt($this->curl,CURLOPT_ENCODING , "");
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');

        $response = curl_exec($this->curl);
        if(curl_errno($this->curl)){
            echo curl_error($this->curl);
        }
        return $this->converterCaracterEspecial($response);
    }

    function baixar_pdf_situacao_fiscal(){
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $this->obter_pagina(false);
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=2^&origem=menu';
        $this->obter_pagina(false);
        $this->url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/default.asp';
        $this->obter_pagina(false);
        $this->url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/default.asp';
        $this->obter_pagina(true,'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/IdentificaUsuario/index.asp', []);
        $this->url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/GerenciaPedido/PedidoConsultaFiscal.asp';
        $this->obter_pagina(false);
        $this->url = 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/Relatorio/GeraRelatorioPdf.asp';
        $this->obter_pagina(false);
        return $this->obter_pdf(false, "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=1&NIPesquisa={$this->numero_documento_procuracao}&Ambiente=2&NICertificado={$this->numero_documento_certificado}&TipoNICertificado=2&SistemaChamador=0101");
    }

    function obter_pdf($is_post = false, $url)
    {

        if ($url != ''){

            $this->url = $url;
        }
        $post_str = http_build_query([
            'ExibeCaptcha' => false, // campos fixos
            'id' => -1, // campos fixos
            'NI' => $this->numero_documento_certificado,
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
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        $fp = fopen ($this->caminho_da_pasta_pdfs."/situação-fiscal-{$this->numero_documento_procuracao}.pdf", 'w+');
        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl))
        {
            echo curl_error($this->curl);
            return false;
        }
        return $this->caminho_da_pasta_pdfs."/situação-fiscal-{$this->numero_documento_procuracao}.pdf";
    }

    function gera_cookie(){
        curl_setopt($this->curl, CURLOPT_URL , 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/IdentificaUsuario/index.asp' );

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        $result = curl_exec($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);
    }

    function obter_simples_nacional_pedidos_parcela(){

        if(strlen($this->numero_documento_procuracao) < 12)
            return;

        $page = $this->obter_pagina(false, 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx');
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
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR);

        $page = $this->simples_nacional_parcela_post($post_fields);
        $html = $html->load($page);
        $table_principal = $html->find('table[id=ctl00_contentPlaceH_wcParc_gdv]', 0);

        if($table_principal){
            return true;
        }
        return false;
    }

    function obter_simples_nacional_emissao_parcela(){
        if(strlen($this->numero_documento_procuracao) < 12)
            return;
        $page = $this->obter_pagina(false, 'https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx');
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
            '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR.'123');

        $page = $this->simples_nacional_emissao_post($post_fields);
        $html = $html->load($page);

        $div_principal = $html->find('div[id=ctl00_contentPlaceH_pnlParcelas]', 0);

        $parcelas = array();
        if($div_principal){
            $linhas = $div_principal->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            foreach ($linhas as $linha){
                $valor = $linha->find('td', 1)->plaintext;
                $data_parcela = $linha->find('td', 0)->plaintext;
                $parcelas[] = array(
                    'valor' => str_replace('R$ ','',str_replace(',','.', $valor)),
                    'data_parcela' => $data_parcela);
            }
        }

        if(count($parcelas) > 0)
            return $parcelas;
        return false;

    }

    function simples_nacional_parcela_post($post){

        curl_setopt($this->curl, CURLOPT_URL,"https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx");
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 1000);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Host: sinac.cav.receita.fazenda.gov.br',
            'Origin: https://sinac.cav.receita.fazenda.gov.br',
            'Referer: https://sinac.cav.receita.fazenda.gov.br/simplesnacional/Aplicacoes/ATSPO/snparc.app/Default.aspx',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36'));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);
        return $response;
    }

    function simples_nacional_emissao_post($post){

        curl_setopt($this->curl, CURLOPT_URL,"https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx");
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(""));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);
        return $this->converterCaracterEspecial($response);
    }
    /**
     * Essa é uma função extra, que pode ser usada para conectar com o codigo de acesso
     * Para ser usada basta substituir no construtor a função conectar_via_codigo_de_certicado por conectar_via_codigo_de_acesso
     * E passar os paramentros numero_documento, codigo_acesso e senha_codigo_acesso
     */
    function conectar_via_codigo_de_acesso(){
        $url_login = $this->login_url ;
        $post_str = http_build_query([
            'ExibeCaptcha' => false, // campos fixos
            'id' => -1, // campos fixos
            'NI' => $this->numero_documento_procuracao,
            'CodigoAcesso' => $this->codigo_acesso,
            'Senha' => $this->senha_codigo_acesso,
            'ExibiuImagem' => true // campos fixos
        ]);
// Primeiramente fazemos login e pegamos o cookie de sessao
        curl_setopt($this->curl, CURLOPT_URL , $url_login );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_str);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "UZ_".uniqid());
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Pragma: no-cache", "Cache-Control: no-cache"));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');

        $response = curl_exec( $this->curl );

        return $response;

    }

    public function converterCaracterEspecial($text){
        return html_entity_decode($text, ENT_QUOTES, "utf-8");
    }

    public function encerrar_conection(){
        curl_close( $this->curl );
    }

    public function obter_numero_documento(){
        return $this->numero_documento_procuracao;
    }
    public function setar_numero_documento($numero){
        return $this->numero_documento_procuracao = $numero;
    }

    public function validar_acesso($response){
        if(!$response)
        {
            echo "============ACESSO NÃO VALIDADO============\n";
            echo "Documento: {$this->numero_documento_procuracao}\n";
            echo "Mensagem do erro: Erro desconhecido.\n";
            echo "===========================================\n";
            return false;
        }

        $html = new Simple_html_dom();
        $html->load($response);

        $div_error = $html->find('div[class=error]', 0);

        if(!is_null($div_error)){
            $codigo_erro = str_replace('Ocorreu um erro. ','', $div_error->find('h1', 0)->plaintext); ;
            $mensagem_erro = $div_error->find('p', 0)->plaintext;
            echo "============ACESSO NÃO VALIDADO============\n";
            echo "Documento: {$this->numero_documento_procuracao}\n";
            echo "Código do erro: {$codigo_erro}\n";
            echo "Mensagem do erro: {$mensagem_erro}\n";
            echo "===========================================\n";

            return false;
        }
        return true;
    }

    public function acesso_valido(){
        return $this->acesso_valido;
    }

    public function trocar_perfil($cnpj){
        $cnpj = $this->apenas_numero( $cnpj );
        curl_setopt($this->curl, CURLOPT_URL,"https://cav.receita.fazenda.gov.br/autenticacao/api/mudarpapel/procuradorpj");
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 1000);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, "={$cnpj}");
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Host: sinac.cav.receita.fazenda.gov.br',
            'Origin: https://sinac.cav.receita.fazenda.gov.br',
            'Referer: https://cav.receita.fazenda.gov.br/ecac/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36'));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);

        $response_json = json_decode($response);
        if(isset($response_json->Value) && strpos($response_json->Value, 'Não existe procuração eletrônica') !== false)
            return false;
        $this->setar_numero_documento($cnpj);
        return $response;
    }

    public function apenas_numero($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

    function __destruct()
    {
        $this->encerrar_conection();
    }
}
