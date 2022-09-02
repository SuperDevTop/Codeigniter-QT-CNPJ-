<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Simple_html_dom.php');
require_once(APPPATH.'libraries/Certificate/Pkcs12.php');

define('SCRIPTSPATH', APPPATH.'libraries'.DIRECTORY_SEPARATOR.'scripts_python');
require 'vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

class Ecac_robo_eprocessos_library {

    /**
     * LOGIN URL
     *
     * URL que faz o login com codigo de acesso
     *
     * @var string
     */
    protected $login_url        = 'https://cav.receita.fazenda.gov.br//autenticacao/Login/CodigoAcesso';

    /**
     * LOGIN URL
     *
     * URL que faz o login com certificado digital A1
     *
     * @var string
     */
    protected $login_url_certificado = 'https://certificado.sso.acesso.gov.br/authorize?response_type=code&client_id=cav.receita.fazenda.gov.br&scope=openid+govbr_recupera_certificadox509+govbr_confiabilidades&redirect_uri=https%3A%2F%2Fcav.receita.fazenda.gov.br%2Fautenticacao%2Flogin%2Fgovbrsso';

    /**
     * CODIGO ACESSO
     *
     * Codigo de acesso pessoal do ecac, usado somente
     * se quiser acessar o portal sem o certificado com o codigo de acesso
     *
     * @var string
     */
    protected $codigo_acesso        = '';

    /**
     * Numero do documento
     *
     * Número do documento CPF ou CNPJ
     *
     * @var string
     */
    protected $numero_documento     = '';

    /**
     * SENHA
     *
     * Senha para o acesso através de codigo de acesso
     *
     * @var string
     */
    protected $senha_codigo_acesso      = '';

    /**
     * PRIVATE_KEY
     *
     * Path para a chave privada do certificado
     *
     * @var string
     */
    protected $private_key      = '';

    /**
     * PUBLIC_KEY
     *
     * Path para chave publica do certificado
     *
     * @var string
     */
    protected $public_key       = '';

    /**
     * CERT_KEY
     *
     * Path para o arquivo cert key
     *
     * @var string
     */
    protected $cert_key     = '';

    /**
     * CERTIFICADO_SENHA
     *
     * Senha do certificado digital
     *
     * @var string
     */
    protected $cerficado_senha      = '';

    /**
     * caminho_certificado
     *
     * caminho do certificado digital
     *
     * @var string
     */
    protected $caminho_certificado      = '';

    /**
     * URL
     *
     * A url que deseja acessar no momento após o login
     *
     * @var string
     */
    protected $url      = '';

    /**
     * CAMINHO_DA_PASTA_PDFS
     *
     * Indica o caminho para a pasta que salva os pdfs
     *
     * @var string
     */
    protected $caminho_da_pasta_pdfs        = '';

    /**
     * ACESSO_VALIDO
     *
     * Valida a conexão feita com o site, caso tenha dado algum erro emite uma mensagem e vai para o próximo
     *
     * @var string
     */
    protected $acesso_valido        = true;

    /**
     * COOKIE_PATH
     *
     * Caminho do cookie
     *
     * @var string
     */
    protected $cookie_path      = "cookie.txt";

    /**
     * path_script_procuracao
     *
     * CAMINHO PARA O SCRIPT procuracao
     *
     * @var string
     */
    protected $path_script_procuracao       = SCRIPTSPATH.DIRECTORY_SEPARATOR.'procuracao.py';
    /**
     * CI Singleton
     *
     * @var object
     */
    /**
     * $path_script_simples_nacional
     *
     * CAMINHO PARA O SCRIPT simples nacional
     *
     * @var string
     */
    protected $path_script_simples_nacional     = SCRIPTSPATH.DIRECTORY_SEPARATOR.'simples_nacional_certificado.py';

    protected $path_script_simples_nacional_debitos     = SCRIPTSPATH.DIRECTORY_SEPARATOR.'simples_nacional_debitos_certificado.py';
    
    protected $CI;

    private $curl;

    private $user_agents = array('Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0',
        'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36','Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2919.83 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2866.71 Safari/537.36','Mozilla/5.0 (X11; Ubuntu; Linux i686 on x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2820.59 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2762.73 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2656.18 Safari/537.36','Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/44.0.2403.155 Safari/537.36','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36','Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2919.83 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2866.71 Safari/537.36', 'Mozilla/5.0 (X11; Ubuntu; Linux i686 on x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2820.59 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2762.73 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2656.18 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/44.0.2403.155 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36', 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 4.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36', 'Mozilla/5.0 (X11; OpenBSD i386) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.3319.102 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2309.372 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2117.157 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.47 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1866.237 Safari/537.36', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/4E423F', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36 Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.517 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.16 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1623.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.17 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36', 'Mozilla/5.0 (X11; CrOS i686 4319.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.2 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1468.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1467.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1464.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1500.55 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.90 Safari/537.36', 'Mozilla/5.0 (X11; NetBSD) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36'
    );

    private $user_agents2 = array('Mozilla/5.0 (Linux; Android 6.0.1; Moto G (4)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-A102U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-N960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; LM-Q720) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; LM-X420) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; LM-Q710(FGN)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/91.0','Mozilla/5.0 (Android 11; Mobile; LG-M255; rv:91.0) Gecko/91.0 Firefox/91.0'
    );

    public function limpa_chaves(){
        $this->public_key = "";
        $this->private_key = "";
        $this->cert_key ="";
        $this->numero_documento = "";
    }
    
    public function __construct($params = array())
    {
        $this->CI =& get_instance();
        $this->curl = curl_init();
        $this->initialize($params);
        try {
            $teste = $this->gerar_chaves();
            if($teste == false){
                $this->limpa_chaves();
                $this->acesso_valido = false;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->limpa_chaves();
            $this->acesso_valido = false;
        }

        $cookie_folder = FCPATH . 'cookies/';
        if (!file_exists($cookie_folder)) {
            mkdir($cookie_folder, 0777, true);
        }
        $this->cookie_path = $cookie_folder . md5(uniqid(rand(), true)). '.txt';
        $fp = fopen($this->cookie_path, 'w');
        fclose($fp);
        chmod($this->cookie_path, 0777);

        if(!$this->conectar_via_certificado()){
            $this->limpa_chaves();
            $this->acesso_valido = false;
        } 


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
//      Gera a cadeia de cerficados do ecac
        $aCerts[] = APPPATH . 'libraries/Certificate/cadeia_certificados_receita/acrfbv3.cer';
        $aCerts[] = APPPATH . 'libraries/Certificate/cadeia_certificados_receita/acserprorfbv3.cer';
        $aCerts[] = APPPATH . 'libraries/Certificate/cadeia_certificados_receita/icpbrasilv2.cer';

        $pkcs = new Pkcs12(APPPATH . 'libraries/Certificate/certificados_clientes/');

        $validacao2 = $pkcs->loadPfxFile($this->caminho_certificado, $this->cerficado_senha);
        if($validacao2 == false){
            return false;
        }
//      adiciona a cadeia ao certificado
        $pkcs->aadChain($aCerts);

//      seta as chaves na classe
        $this->public_key = $pkcs->pubKeyFile;
        $this->private_key = $pkcs->priKeyFile;
        $this->cert_key = $pkcs->certKeyFile;
        $this->numero_documento = $pkcs->cnpj;
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

    function obter_cookies($response){
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        return $cookies;
    }
    
    function conectar_via_certificado(){
        $this->configurar();
        $url_login = $this->login_url_certificado ;
        curl_setopt($this->curl, CURLOPT_URL , $url_login );
        $response = curl_exec( $this->curl );
        $cookies = $this->obter_cookies($response);
        if(isset($cookies['ASP_NET_SessionId']))
            $this->aspsession =$cookies['ASP_NET_SessionId'];

        $postfields = array(
             'TokenLabel' => '',
             'id'=>'-1',
             'GoogleCaptchaTokenLoginGovBR' => '',
             'h-captcha-response' => 'aa'
         );

        curl_setopt($this->curl, CURLOPT_URL , 'https://cav.receita.fazenda.gov.br/autenticacao/Login/IndexGovBr' );
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);

        $headers = array(
         "Connection: keep-alive",
         "Cache-Control: max-age=0",
         'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
           "sec-ch-ua-mobile: ?0",
           'sec-ch-ua-platform: "Windows"',
           "Upgrade-Insecure-Requests: 1",
           "Origin: https://cav.receita.fazenda.gov.br",
           "Content-Type: application/x-www-form-urlencoded",
           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
           "Sec-Fetch-Site: same-origin",
           "Sec-Fetch-Mode: navigate",
           "Sec-Fetch-User: ?1",
           "Sec-Fetch-Dest: document",
           "Referer: https://cav.receita.fazenda.gov.br/autenticacao/login",
           "Accept-Language: pt-BR,pt;q=0.9",
        );
          curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

         $response = curl_exec( $this->curl );
         if (curl_errno($this->curl))
             echo curl_error($this->curl);
        $cookies = $this->obter_cookies($response);
        if(isset($cookies['COOKIECAV']))
            $this->cookiecav = $cookies['COOKIECAV'];
        curl_setopt($this->curl, CURLOPT_HEADER, 0);

        $acesso_valido = $this->validar_acesso($this->converterCaracterEspecial($response));
        return $acesso_valido;
    }

    public function configurar(){
        $this->curl = curl_init();
        // $username = 'lum-customer-c_aeeb4574-zone-residential';
        // $password = 'xcop8rz17amg';
        // $port = 22225;
        // $session = mt_rand();
        // $super_proxy = 'zproxy.lum-superproxy.io';
        // curl_setopt($this->curl, CURLOPT_PROXY, "http://$super_proxy:$port");
        // curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, "$username-country-br-session-$session:$password");
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->user_agents[rand(0, count($this->user_agents) - 1)]);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Pragma: no-cache", "Cache-Control: no-cache",'Content-type: text/html; charset=UTF-8'));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($this->curl, CURLOPT_SSLCERT, $this->public_key);
        curl_setopt($this->curl, CURLOPT_SSLKEY, $this->private_key);
        curl_setopt($this->curl, CURLOPT_CAINFO, $this->cert_key);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_PORT , 443);
    }


    public


    function obter_mensagem_caixa_postal(){

        $this->url = 'https://cav.receita.fazenda.gov.br/Servicos/ATSDR/CaixaPostal.app/Action/ListarMensagemAction.aspx';
        $page = $this->obter_pagina(false);
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

        } catch (Exception $er) {
            
            return false;
        }
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
                'NI' => $this->numero_documento,
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
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
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

    function baixar_pdf_situacao_fiscal(){


//      Gerar essa configuração é necessaria porque ele faz um pre-processamento do pdf da situação fiscal, se chamar  a rotina de obter
//      o pdf sem fazer isso, o pdf vem vazio.
        $this->gera_configuracao_pdf("https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/GerenciaPedido/PedidoConsultaFiscal.asp?IndNovaConsulta=true&OpConsulta=1");
        $urlPF = "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=2&NIPesquisa={$this->numero_documento}&Ambiente=2&NICertificado={$this->numero_documento}&TipoNICertificado=1&SistemaChamador=0103";
        $urlPJ = "https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/IntegraSitfis/RelatorioEcac.aspx?TipoNIPesquisa=1&NIPesquisa={$this->numero_documento}&Ambiente=2&NICertificado={$this->numero_documento}&TipoNICertificado=2&SistemaChamador=0101";
        return $this->obter_pdf(false, strlen($this->numero_documento) > 11 ? $urlPJ : $urlPF);
    }

    function obter_pdf($is_post = false, $url)
    {

        //DEFINE A DATA PARA GRAVAR NO ARQUIVO
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Y-m-d');

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
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        $fp = fopen ($this->caminho_da_pasta_pdfs."/situação-fiscal-".$data_atual."-{$this->numero_documento}.pdf", 'w+');
        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl))
        {
            echo curl_error($this->curl);
            return false;
        }
        $caminho_local = $this->caminho_da_pasta_pdfs."/situação-fiscal-".$data_atual."-{$this->numero_documento}.pdf";
        $caminho_salvar = str_replace('/var/www/html/', '', $caminho_local);
        $caminho_salvar_extra = str_replace('//', '/', $caminho_salvar);
        $this->upload_google($caminho_local, $caminho_salvar_extra );
        $a = array();
        array_push($a, $caminho_local);
        array_push($a, $caminho_salvar_extra);
        return $a;
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

        if(strlen($this->numero_documento) < 12)
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
        if(strlen($this->numero_documento) < 12)
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
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);
        return $response;
        curl_close ($this->curl);
    }

    function simples_nacional_emissao_post($post){

        curl_setopt($this->curl, CURLOPT_URL,"https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/snparc.app/Default.aspx");
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(""));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);
        return $this->converterCaracterEspecial($response);
        curl_close ($this->curl);
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
            'NI' => $this->numero_documento,
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
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Pragma: no-cache", "Cache-Control: no-cache"));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);

        $response = curl_exec( $this->curl );

        return $response;

    }

    public function converterCaracterEspecial($text){
        return html_entity_decode($text, ENT_QUOTES, "utf-8");
    }

    public function encerrar_conection(){
        if( ! is_null($this->curl) ){
            curl_close($this->curl);
            $this->curl = null;
        }
    }

    public function obter_numero_documento(){
        return $this->numero_documento;
    }
    public function setar_numero_documento($numero){
        return $this->numero_documento = $numero;
    }

    public function validar_acesso($response){
        if(!$response)
        {
            echo "============ACESSO NÃO VALIDADO============\n";
            echo "Documento: {$this->numero_documento}\n";
            echo "Mensagem do erro: Erro desconhecido.\n";
            echo "===========================================\n";
            return false;
        }

        $html = new Simple_html_dom();
        $html->load($response);

        $div_error = $html->find('div[class=error]', 0);

        $tag_a_perfil = NULL; 
        $tag_a_perfil = $html->find('div[id=cabecalho]');
        
        if(!empty($tag_a_perfil)){
            // echo $tag_a_perfil;
            // echo 'Logado';
        }else{
            return false;
            //echo "Não logado";
        }
        
        if(!is_null($div_error)){
            $codigo_erro = str_replace('Ocorreu um erro. ','', $div_error->find('h1', 0)->plaintext); ;
            $mensagem_erro = $div_error->find('p', 0)->plaintext;
            echo "============ACESSO NÃO VALIDADO============\n";
            echo "Documento: {$this->numero_documento}\n";
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
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl))
            echo curl_error($this->curl);
        return $response;
        curl_close ($this->curl);
    }

    function baixar_pdf_cadin()
    {   
        //DEFINE A DATA PARA GRAVAR NO ARQUIVO
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Y-m-d');
        
        $headers = array("Connection: keep-alive",
            "Upgrade-Insecure-Requests: 1" ,
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36" ,
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9" ,
            "Sec-Fetch-Site: same-origin" ,
            "Sec-Fetch-Mode: navigate" ,
            "Sec-Fetch-User: ?1" ,
            "Sec-Fetch-Dest: iframe" ,
            "Referer: https://sic.cav.receita.fazenda.gov.br/precadin-internet/home.html" ,
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7");
        curl_setopt($this->curl, CURLOPT_URL , 'https://sic.cav.receita.fazenda.gov.br/precadin-internet/api/contribuinterepresentado/relatoriodevedor/pdf?' );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        $fp = fopen ($this->caminho_da_pasta_pdfs."/cadin-".$data_atual."-{$this->numero_documento}.pdf", 'w+');
        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl))
        {
            echo curl_error($this->curl);
            return false;
        }
        $caminho_local = $this->caminho_da_pasta_pdfs."/cadin-".$data_atual."-{$this->numero_documento}.pdf";
        $caminho_salvar = str_replace('/var/www/html/', '', $caminho_local);
        $caminho_salvar_extra = str_replace('//', '/', $caminho_salvar);
        $this->upload_google($caminho_local, $caminho_salvar_extra );
        $a = array();
        array_push($a, $caminho_local);
        array_push($a, $caminho_salvar_extra);
        return $a;
    }

    public function get_eprocessos_ativos(){
        $url = "https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos?solidario=false";

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 0);
        curl_setopt($this->curl, CURLOPT_POST, 0);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 0);

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: none",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($this->curl);
        $dados = json_decode( $response, true );
        return $dados;
    }

    public function get_eprocessos_inativos(){
        $url = 'https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos/inativos';

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 0);
        curl_setopt($this->curl, CURLOPT_POST, 0);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 0);

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: none",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($this->curl);
        echo $response;
        $dados = json_decode( $response, true );
        return $dados;
    }

    public function get_eprocesso_historico($numero_processo){
        $url = 'https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos/'.$numero_processo.'/historico/';

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 0);
        curl_setopt($this->curl, CURLOPT_POST, 0);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 0);

        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            "sec-ch-ua-mobile: ?0",
            "Upgrade-Insecure-Requests: 1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: none",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9",
        );
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($this->curl);
        $dados = json_decode( $response, true );
        return $dados;
    }

    public function get_COOKIECAV(){
        $arquivo = fopen ($this->cookie_path, 'r');
        while(!feof($arquivo))
        {
            $linha = fgets($arquivo, 1024);
            if (strpos($linha, 'COOKIECAV') !== false){
                $array =  explode('COOKIECAV', $linha);
                return trim($array[1]);
            }
        }
        fclose($arquivo);
    }

    public function get_dctf($myhashmap){
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $this->obter_pagina(false);
        $this->obter_pagina(false, 'https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=14&origem=menu');

        $page = $this->obter_pagina(false, 'https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/Abrir.asp');
        $html = new Simple_html_dom();
        $html->load($page);
        $tbDeclaracoes = $html->find('table[id=tbDeclaracoes]', 0);
        $declaracoes = array();
        if($tbDeclaracoes){
            $linhas = $tbDeclaracoes->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            foreach ($linhas as $linha){
                $periodo_inicial = trim( $linha->find('td', 3)->plaintext );
                $periodo_inicial = date('Y-m-d', strtotime(str_replace('/', '-', $periodo_inicial)));
                if($periodo_inicial < date('Y-m-d', strtotime('01-01-2020')))
                    continue;

                $tipo = trim( $linha->find('td', 6)->plaintext );
                $pos = strpos('Cancelada', $tipo);

                if ($pos === false) {
                    
                } else {
                    continue;
                }
                
                if(isset($myhashmap[$this->apenas_numero(trim( $linha->find('td', 0)->plaintext ))."/".trim( $linha->find('td', 1)->plaintext )])){
                    continue;
                }
                
                $declaracoes[] = array(
                    'cnpj' => $this->apenas_numero(trim( $linha->find('td', 0)->plaintext )),
                    'cnpj_formatado' => trim( $linha->find('td', 0)->plaintext ),
                    'periodo' => trim( $linha->find('td', 1)->plaintext ),
                    'data_recepcao' => trim( $linha->find('td', 2)->plaintext ),
                    'periodo_inicial' => trim( $linha->find('td', 3)->plaintext ),
                    'periodo_final' => trim( $linha->find('td', 4)->plaintext ),
                    'situacao' => trim( $linha->find('td', 5)->plaintext ),
                    'tipo_status' => trim( $linha->find('td', 6)->plaintext ),
                    'numero_declaracao' =>  '',
                    'numero_recibo' => '',
                    'data_processamento' => '',
                );
            }
        }
        return $declaracoes;
    }

    public function get_dctf_declaracao(){
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $this->obter_pagina(false);
        $this->obter_pagina(false, 'https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=14&origem=menu');

        $page = $this->obter_pagina(false, 'https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/Abrir.asp');
        $html = new Simple_html_dom();
        $html->load($page);
        $tbDeclaracoes = $html->find('table[id=tbDeclaracoes]', 0);
        $declaracoes = array();

        if($tbDeclaracoes){
            $linhas = $tbDeclaracoes->find('tr');
            array_shift($linhas); // remove a primeira linha, porque é o cabeçalho da table
            $contador = 0;
            foreach ($linhas as $linha){
                $periodo_inicial = trim( $linha->find('td', 3)->plaintext );
                $periodo_inicial = date('Y-m-d', strtotime(str_replace('/', '-', $periodo_inicial)));
                if($periodo_inicial < date('Y-m-d', strtotime('01-01-2020')))
                    continue;
                $input = $linha->find('td', 7)->find('input', 0);
                $string_replace = str_replace("return selecionaServico", "", $input->onclick);
                $string_replace = str_replace("'", "", $string_replace);
                $string_replace = str_replace("(", "", $string_replace);
                $string_replace = str_replace(")", "", $string_replace);
                $parameters = explode(',', $string_replace);
                $var1 = trim($parameters[0]);
                $var2 = trim($parameters[1]);
                $total_linhas = count($linhas);
                $post_str = "";
                for ($i=0; $i < $total_linhas; $i++){

                    if($i == $contador)
                        $post_str .= "ND={$var2}";
                    else
                        $post_str .= "ND=%23";

                    $ultimo = $i == ($total_linhas - 1);

                    if(!$ultimo)
                        $post_str .= '&';
                }
                $contador++;
                $UltimoSel = str_pad( $this->apenas_numero($var1) , 4 , '0' , STR_PAD_LEFT);
                $this->post_dctf(
                    'https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/Inicio_Impr.asp?UltimoSel='. $UltimoSel,
                    $post_str,
                    array(
                        "Connection: keep-alive",
                        "Cache-Control: max-age=0",
                        "Upgrade-Insecure-Requests: 1",
                        "Origin: https://cav.receita.fazenda.gov.br",
                        "Content-Type: application/x-www-form-urlencoded",
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36",
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                        "Sec-Fetch-Site: same-origin",
                        "Sec-Fetch-Mode: navigate",
                        "Sec-Fetch-User: ?1",
                        "Sec-Fetch-Dest: iframe",
                        "Referer: https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/Abrir.asp",
                        "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
                    )
                );
                $page = $this->obter_pagina(false, 'https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/ImprAbrir.asp');
                try
                {
                    $html = str_replace('Â','', utf8_encode($page));
                    ini_set('max_execution_time', 0);
                    $mpdf = new \Mpdf\Mpdf();
                    $mpdf->WriteHTML($html);
                    $mpdf->Output($this->caminho_da_pasta_pdfs."{$this->obter_numero_documento()}-dctf-declaracao-{$UltimoSel}.pdf",'F');
                }catch (Exception $e){
                    echo $e;
                }
                $declaracoes[] = array(
                    'cnpj' => $this->apenas_numero(trim( $linha->find('td', 0)->plaintext )),
                    'cnpj_formatado' => trim( $linha->find('td', 0)->plaintext ),
                    'periodo' => trim( $linha->find('td', 1)->plaintext ),
                    'data_recepcao' => trim( $linha->find('td', 2)->plaintext ),
                    'periodo_inicial' => trim( $linha->find('td', 3)->plaintext ),
                    'periodo_final' => trim( $linha->find('td', 4)->plaintext ),
                    'situacao' => trim( $linha->find('td', 5)->plaintext ),
                    'tipo_status' => trim( $linha->find('td', 6)->plaintext ),
                    'numero_declaracao' =>  '',
                    'numero_recibo' => '',
                    'data_processamento' => '',
                    'caminho_download_declaracao' => $this->caminho_da_pasta_pdfs."{$this->obter_numero_documento()}-dctf-declaracao-{$UltimoSel}.pdf"
                );
            }
        }
        return $declaracoes;
    }

    public function post_dctf($url, $post_str)
    {
        curl_setopt($this->curl, CURLOPT_URL,$url);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_str);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Accept-Encoding: gzip, deflate, br",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Cache-Control: max-age=0",
            "Connection: keep-alive",
            "Content-Length: 146",
            "Content-Type: application/x-www-form-urlencoded",
            "Host: cav.receita.fazenda.gov.br",
            "Origin: https://cav.receita.fazenda.gov.br",
            "Referer: https://cav.receita.fazenda.gov.br/Servicos/ATSPO/DCTF/Consulta/Abrir.asp",
            "Sec-Fetch-Dest: iframe",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-User: ?1",
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36"));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, false);

        $response = curl_exec ($this->curl);
        if(curl_errno($this->curl)){
            $erro = curl_error($this->curl);
            if (strpos($erro, 'reset by peer') !== false){
//                echo 'erro 1002';
//                return false;
            }
        }

        return $this->converterCaracterEspecial($response);
    }

    public function get_divida_ativa_nao_previdenciaria(){
    
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $this->obter_pagina(false);

        $headers = array( "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Accept-Encoding: gzip, deflate, br",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Connection: keep-alive",
            "Host: cav.receita.fazenda.gov.br",
            "Sec-Fetch-Dest: document",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-Site: none",
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"
        );
        curl_setopt($this->curl, CURLOPT_URL , 'https://cav.receita.fazenda.gov.br/Servicos/ATBHE/PGFN/acompanharRequerimentos/app.aspx' );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_ENCODING, '');
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl)){
            echo curl_error($this->curl);
        }

        $html = new Simple_html_dom();
        $html->load($response);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }
        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];
        $__EVENTVALIDATION = $vals[2];
        $mensagemComBase64 = $vals[3];

        $array = array();

        #$response = shell_exec("sudo python {$this->path_script_divida_ativa_nao_previdenciaria} \"{$__VIEWSTATE}\" \"{$__VIEWSTATEGENERATOR}\" \"{$__EVENTVALIDATION}\" \"{$mensagemComBase64}\" \"{$this->driver_executable_path}\" ");
        $post = [
                'VIEWSTATE' => $__VIEWSTATE ,
                'VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                'EVENTVALIDATION' => $__EVENTVALIDATION ,
                'mensagemComBase64' => $mensagemComBase64 ,
        ];
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://0.0.0.0:5000/divida_ativa_nao_previdenciaria");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
    
    if($response == "ERRO")
            return $array;
        $html = new Simple_html_dom();
        $html->load($response);


        $tabelaInscJaParceladas = $html->find('tbody[id=inscricoesForm:tabelaInscPassiveisParcelamentoSisparTab:tb]', 0);
        if($tabelaInscJaParceladas){
            $linhas = $tabelaInscJaParceladas->find('tr');
            foreach ($linhas as $linha){
                $numero_inscricao = isset($linha->find('td', 0)->plaintext) ? trim($linha->find('td', 0)->plaintext) : '';
                if (is_null($numero_inscricao) || $numero_inscricao == '')
                    continue;
                $numero_processo = isset($linha->find('td', 1)->plaintext) ? trim($linha->find('td', 1)->plaintext) : '';
                $cnpj_devedor_principal = isset($linha->find('td', 2)->plaintext) ? trim($linha->find('td', 2)->plaintext) : '';
                $situacao = trim($linha->find('td', 3)->plaintext);
                $valor_consolidado = isset($linha->find('td', 4)->plaintext) ? trim($linha->find('td', 4)->plaintext) : '';
                $data_consolidacao = isset($linha->find('td', 5)->plaintext) ? trim($linha->find('td', 5)->plaintext) : '';
                $array[] = array(
                    'cnpj' => $this->obter_numero_documento(),
                    'numero_inscricao' => $numero_inscricao,
                    'numero_processo' => $numero_processo,
                    'cnpj_devedor_principal' => $cnpj_devedor_principal,
                    'situacao' => $situacao,
                    'valor_consolidado' => $valor_consolidado,
                    'data_consolidacao' => $data_consolidacao,
                    'extinta' => 'NAO',
                );
            }
        }

        $tabelaInscPassiveisParcelamentoSispar = $html->find('tbody[id=inscricoesForm:tabelaInscJaParceldasTab:tb]', 0);
        if($tabelaInscPassiveisParcelamentoSispar){
            $linhas = $tabelaInscPassiveisParcelamentoSispar->find('tr');
            foreach ($linhas as $linha){
                $numero_inscricao = isset($linha->find('td', 0)->plaintext) ? trim($linha->find('td', 0)->plaintext) : '';
                if (is_null($numero_inscricao) || $numero_inscricao == '')
                    continue;
                $numero_processo = isset($linha->find('td', 1)->plaintext) ? trim($linha->find('td', 1)->plaintext) : '';
                $cnpj_devedor_principal = isset($linha->find('td', 2)->plaintext) ? trim($linha->find('td', 2)->plaintext) : '';
                $situacao = isset($linha->find('td', 3)->plaintext) ? trim($linha->find('td', 3)->plaintext) : '';

                $array[] = array(
                    'cnpj' => $this->obter_numero_documento(),
                    'numero_inscricao' => $numero_inscricao,
                    'numero_processo' => $numero_processo,
                    'cnpj_devedor_principal' => $cnpj_devedor_principal,
                    'situacao' => $situacao,
                    'extinta' => 'SIM',
                );
            }
        }

        return $array;
    }

    public function get_divida_ativa_previdenciaria(){
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $this->obter_pagina(false);

        $headers = array( "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Accept-Encoding: gzip, deflate, br",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Connection: keep-alive",
            "Host: cav.receita.fazenda.gov.br",
            "Sec-Fetch-Dest: document",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-Site: none",
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"
        );
        curl_setopt($this->curl, CURLOPT_URL , 'https://cav.receita.fazenda.gov.br/Servicos/ATBHE/PGFN/acompanharRequerimentos/app.aspx' );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_ENCODING, '');
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl)){
            echo curl_error($this->curl);
        }

        $html = new Simple_html_dom();
        $html->load($response);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }
        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];
        $__EVENTVALIDATION = $vals[2];
        $mensagemComBase64 = $vals[3];

        $array = array();
        $post = [
                'VIEWSTATE' => $__VIEWSTATE ,
                'VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                'EVENTVALIDATION' => $__EVENTVALIDATION ,
                'mensagemComBase64' => $mensagemComBase64 ,
        ];


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://0.0.0.0:5000/divida_ativa_previdenciaria");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        if($response == "ERRO")
            return $array;
        $html = new Simple_html_dom();
        $html->load($response);
        $tabelaDebcads = $html->find('table[id=debcadsForm:tabelaDebcadsTab]', 0);
        if($tabelaDebcads){
            $linhas = $tabelaDebcads->find('tr');
            array_shift($linhas);
            array_shift($linhas);
            foreach ($linhas as $linha){
                $numero_inscricao = isset($linha->find('td', 0)->plaintext) ? trim($linha->find('td', 0)->plaintext) : '';
                if (is_null($numero_inscricao) || $numero_inscricao == '')
                    continue;
                $cnpj_devedor_principal = isset($linha->find('td', 1)->plaintext) ? trim($linha->find('td', 1)->plaintext) : '';
                $devedor_principal = isset($linha->find('td', 2)->plaintext) ? trim($linha->find('td', 2)->plaintext) : '';
                $fase_atual = isset($linha->find('td', 3)->plaintext) ? trim($linha->find('td', 3)->plaintext) : '';
                $valor_total_debito = isset($linha->find('td', 4)->plaintext) ? trim($linha->find('td', 4)->plaintext) : '';

                $array[] = array(
                    'cnpj' => $this->obter_numero_documento(),
                    'numero_inscricao' => $numero_inscricao,
                    'cnpj_devedor_principal' => $cnpj_devedor_principal,
                    'devedor_principal' => $devedor_principal,
                    'fase_atual' => $fase_atual,
                    'valor_total_debito' => $valor_total_debito,
                );
            }
        }
        return $array;
    }

    public function get_divida_ativa_fgts(){
        $this->url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $this->obter_pagina(false);

        $headers = array( "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Accept-Encoding: gzip, deflate, br",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Connection: keep-alive",
            "Host: cav.receita.fazenda.gov.br",
            "Sec-Fetch-Dest: document",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-Site: none",
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"
        );
        curl_setopt($this->curl, CURLOPT_URL , 'https://cav.receita.fazenda.gov.br/Servicos/ATBHE/PGFN/acompanharRequerimentos/app.aspx' );
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($this->curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_ENCODING, '');
        $response = curl_exec($this->curl);
        if(curl_errno($this->curl)){
            echo curl_error($this->curl);
        }

        $html = new Simple_html_dom();
        $html->load($response);

        $nodes = $html->find("input[type=hidden]");
        $vals = array();
        foreach ($nodes as $node) {
            $val = $node->value;
            if(!empty($val) && !is_null($val))
                $vals[] = $val;
        }
        $__VIEWSTATE = $vals[0];
        $__VIEWSTATEGENERATOR = $vals[1];
        $__EVENTVALIDATION = $vals[2];
        $mensagemComBase64 = $vals[3];

        $array = array();
        $post = [
                'VIEWSTATE' => $__VIEWSTATE ,
                'VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                'EVENTVALIDATION' => $__EVENTVALIDATION ,
                'mensagemComBase64' => $mensagemComBase64 ,
        ];


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://0.0.0.0:5000/divida_ativa_fgts");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        if($response == "ERRO")
            return $array;
        $html = new Simple_html_dom();
        $html->load($response);


        $tabelaFgts = $html->find('table[id=fgtsForm:tabelaFgtsTab]', 0);
        if($tabelaFgts){
            $linhas = $tabelaFgts->find('tr');
            array_shift($linhas);
            array_shift($linhas);

            foreach ($linhas as $linha){
                $numero_inscricao = isset($linha->find('td', 0)->plaintext) ? trim($linha->find('td', 0)->plaintext) : '';
                if (is_null($numero_inscricao) || $numero_inscricao == '')
                    continue;
                $cnpj_devedor_principal = isset($linha->find('td', 1)->plaintext) ? trim($linha->find('td', 1)->plaintext) : '';
                $devedor_principal = isset($linha->find('td', 2)->plaintext) ? trim($linha->find('td', 2)->plaintext) : '';
                $situacao = isset($linha->find('td', 3)->plaintext) ? trim($linha->find('td', 3)->plaintext) : '';
                $valor_total_debito = isset($linha->find('td', 4)->plaintext) ? trim($linha->find('td', 4)->plaintext) : '';

                $array[] = array(
                    'cnpj' => $this->obter_numero_documento(),
                    'numero_inscricao' => $numero_inscricao,
                    'cnpj_devedor_principal' => $cnpj_devedor_principal,
                    'devedor_principal' => $devedor_principal,
                    'situacao' => $situacao,
                    'valor_total_debito' => $valor_total_debito,
                );
            }
        }

        return $array;
    }
    public function apenas_numero($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

    public function get_procuracoes(){
        $this->encerrar_conection();
        $response = shell_exec("python {$this->path_script_procuracao} \"{$this->cookie_path}\" ");
        $json  = json_decode($response, TRUE);
        return $json;
    }

    public function get_das($cnpj='', $ano=''){
        $this->encerrar_conection();

        if ($ano == '')
            // $ano = date("Y");
            $ano = "2021";
        $this->setar_numero_documento($cnpj);
        $response = shell_exec("python {$this->path_script_simples_nacional} \"{$this->cookie_path}\" \"{$this->caminho_da_pasta_pdfs}\" \"{$ano}\"");
        echo var_export(json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true));
        return json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }

    public function get_das_debitos($cnpj=''){
        $this->encerrar_conection();
        $response = shell_exec("python {$this->path_script_simples_nacional_debitos} \"{$this->cookie_path}\"");
        return json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }

    public function get_simplesnacional_pedidos_parcelamentos(){
        $ch = curl_init();
        $post = array(
            'folder_pdf' => $this->caminho_da_pasta_pdfs,
            'cert_key' => $this->cert_key,
        );
        curl_setopt($ch, CURLOPT_URL, "http://0.0.0.0:5000/simplesnacional-parcelamento");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        return json_decode($response, true);
    }

    function upload_google($path_upload, $file_name)
    {
        $storage = new  StorageClient ([
            'keyFile' => json_decode ( file_get_contents ( FCPATH.'/windy-hangar-321019-daae49ffa513.json' ), true ),
            'projectId' => 'windy-hangar-321019'
        ]);

        $source = $path_upload;
        $file = fopen($source, 'r');
        $bucket = $storage->bucket('cron-veri-files-br');
        $bucket->upload($file, [
            'name' => $file_name
        ]);
    }

    function __destruct()
    {
        $this->encerrar_conection();
    }
}
