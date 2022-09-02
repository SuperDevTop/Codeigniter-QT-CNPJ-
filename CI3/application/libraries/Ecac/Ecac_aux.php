<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Simple_html_dom.php');

define('SCRIPTSPATH', APPPATH.'libraries'.DIRECTORY_SEPARATOR.'scripts_python');

class Ecac_aux {

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
     * Numero do documento procuracao
     *
     * Número do documento CPF ou CNPJ
     *
     * @var string
     */
    protected $numero_documento_procuracao      = '';

    /**
     * Numero do documento certificado
     *
     * Número do documento CPF ou CNPJ
     *
     * @var string
     */
    protected $numero_documento_certificado     = '';

    /**
     * SENHA
     *
     * Senha para o acesso através de codigo de acesso
     *
     * @var string
     */
    protected $senha_codigo_acesso      = '';

    /**
     * CERTIFICADO
     *
     * certificado com todas as informações
     *
     * @var string
     */
    protected $certificado      = '';

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
     * DRIVER_EXECUTABLE_PATH
     *
     * CAMINHO PARA O ARQUIVO DE DRIVER UTILIZADO NOS SCRIPTS EM PYTHON
     *
     * @var string
     */

    protected $driver_executable_path       = '';

    /**
     * path_script_divida_ativa_nao_previdenciaria
     *
     * CAMINHO PARA O SCRIPT divida ativa nao previdenciaria
     *
     * @var string
     */
    protected $path_script_divida_ativa_nao_previdenciaria      = SCRIPTSPATH.DIRECTORY_SEPARATOR.'teste.py';

    /**
     * path_script_divida_ativa_previdenciaria
     *
     * CAMINHO PARA O SCRIPT divida ativa  previdenciaria
     *
     * @var string
     */
    protected $path_script_divida_ativa_previdenciaria      = SCRIPTSPATH.DIRECTORY_SEPARATOR.'divida_ativa_previdenciaria.py';

    /**
     * path_script_divida_ativa_fgts
     *
     * CAMINHO PARA O SCRIPT divida ativa fgts
     *
     * @var string
     */
    protected $path_script_divida_ativa_fgts        = SCRIPTSPATH.DIRECTORY_SEPARATOR.'divida_ativa_fgts.py';

    /**
     * $path_script_eprocessos_ativos
     *
     * CAMINHO PARA O SCRIPT eprocessos ativos
     *
     * @var string
     */
    protected $path_script_eprocessos_ativos        = SCRIPTSPATH.DIRECTORY_SEPARATOR.'eprocessos_ativos.py';

    /**
     * $path_script_eprocessos_inativos
     *
     * CAMINHO PARA O SCRIPT eprocessos inativos
     *
     * @var string
     */
    protected $path_script_eprocessos_inativos      = SCRIPTSPATH.DIRECTORY_SEPARATOR.'eprocessos_inativos.py';

    /**
     * $path_script_eprocessos_ativos
     *
     * CAMINHO PARA O SCRIPT eprocessos historico
     *
     * @var string
     */
    protected $path_script_eprocessos_historico     = SCRIPTSPATH.DIRECTORY_SEPARATOR.'eprocessos_historico.py';

    /**
     * $path_script_simples_nacional
     *
     * CAMINHO PARA O SCRIPT simples nacional
     *
     * @var string
     */
    protected $path_script_simples_nacional     = SCRIPTSPATH.DIRECTORY_SEPARATOR.'simples_nacional.py';

    /**
     * $path_script_simples_nacional_debitos
     *
     * CAMINHO PARA O SCRIPT simples nacional
     *
     * @var string
     */
    protected $path_script_simples_nacional_debitos     = SCRIPTSPATH.DIRECTORY_SEPARATOR.'simples_nacional_debitos.py';

    /**
     * CI Singleton
     *
     * @var object
     */
    protected $CI;

    protected $cookiecav = '';

    protected $aspsession = '';

    protected $cookies;

    protected $servidores=['216.238.75.208'];

    protected $start_sessao = true;


    private $user_agents = array('Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0',
        'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36','Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2919.83 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2866.71 Safari/537.36','Mozilla/5.0 (X11; Ubuntu; Linux i686 on x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2820.59 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2762.73 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2656.18 Safari/537.36','Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/44.0.2403.155 Safari/537.36','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36','Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36','Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2919.83 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2866.71 Safari/537.36', 'Mozilla/5.0 (X11; Ubuntu; Linux i686 on x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2820.59 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2762.73 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2656.18 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/44.0.2403.155 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.4; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2225.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2224.3 Safari/537.36', 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 4.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36', 'Mozilla/5.0 (X11; OpenBSD i386) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.3319.102 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2309.372 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2117.157 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.47 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1866.237 Safari/537.36', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/4E423F', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36 Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.517 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.16 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1623.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.17 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36', 'Mozilla/5.0 (X11; CrOS i686 4319.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.2 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1468.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1467.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1464.0 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1500.55 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.90 Safari/537.36', 'Mozilla/5.0 (X11; NetBSD) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36'
    );

    private $user_agents2 = array('Mozilla/5.0 (Linux; Android 6.0.1; Moto G (4)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-A102U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; SM-N960U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; LM-Q720) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; LM-X420) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Linux; Android 11; LM-Q710(FGN)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36','Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/91.0','Mozilla/5.0 (Android 11; Mobile; LG-M255; rv:91.0) Gecko/91.0 Firefox/91.0'
    );

    public function __construct($params = array(), $conectar = true)
    {

        $this->CI =& get_instance();
        $this->initialize($params);
        $cookie_folder = FCPATH . 'cookies/';
        if (!file_exists($cookie_folder)) {
            mkdir($cookie_folder, 0777, true);
        }
        $this->cookie_path = $cookie_folder . md5(uniqid(rand(), true)). '.txt';
        $this->CI->load->helper('googlestorage_helper');
        if($this->start_sessao){
            $this->carregar_sessao();
        }
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

    public function buscar_sessao_ativa(){
        $host ="216.238.70.192";
        $user = "usuarioacesso";
        $pass = "1*Databyte*1";
        $db = 'bancosessoes';

        $con = mysqli_connect($host, $user, $pass, $db);
        mysqli_set_charset($con,"utf8");
        mysqli_query($con,"SET NAMES 'utf8'");
        mysqli_query($con,'SET character_set_connection=utf8');
        mysqli_query($con,'SET character_set_client=utf8');
        mysqli_query($con,'SET character_set_results=utf8');

        $local_arquivo = 'https://demo.veri-sp.com.br/crons-api/'.$this->certificado->caminho_arq;

        $query = "SELECT * FROM dtb_file_login as a where a.path = '".$local_arquivo."' AND TIMESTAMPDIFF(MINUTE , data_LOGIN, now()) <= 30 ";
        $dados = mysqli_query($con, $query);

        $existe = false;
        while($dados_Login = mysqli_fetch_assoc($dados)){
            $existe = true;
            $this->cookiecav= $dados_Login['cookiecav'];
            $this->aspsession=$dados_Login['aspsession'];

            break;
        }

        return $existe;
    }

    public function carregar_sessao(){
 
        $raw = http_build_query(
        array(
            'path' => 'https://demo.veri-sp.com.br/crons-api/'.$this->certificado->caminho_arq,
            'senha' => $this->certificado->pass,
            'cn' => $this->certificado->cn,
            )
        ) ;
        $ip = $this->servidores[rand(0, count($this->servidores)-1)];

        $url = "http://$ip/createTask?$raw";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          

        $resp = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($resp, true);

        if (!isset($data['taskId']))
            return false;

        $taskId = $data['taskId'];

        $tentativas = 0;
        $raw = http_build_query(
            array(
                'taskId' => $taskId,
            )
        ) ;
        echo "taskid:$taskId"."<br>";
        sleep(15);
        while (true){
            $url = "http://$ip/getTaskResult?$raw";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $resp = curl_exec($curl);
            $resp = json_decode($resp, true);
            print_r($resp);
            echo '<br>';
            curl_close($curl);
            if (isset($resp['status']) && $resp['status']==1) 
            {
                $this->cookiecav=$resp['data']['cookiecav'];
                $this->aspsession=$resp['data']['aspsession'];
                break;
            }
            $tentativas= $tentativas + 1;
            if ($tentativas > 10000)
                break;
            sleep(5);
        }         
    }

    public function setProxy($curl){
        $username = 'lum-customer-c_aeeb4574-zone-residential';
        $password = '1*dATaVeR1pR0xY*_1';
        $port = 22225;
        $session = mt_rand();
        $super_proxy = 'zproxy.lum-superproxy.io';
        curl_setopt($curl, CURLOPT_PROXY, "http://$super_proxy:$port");
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$username-country-br-session-$session:$password");
    }

    public function getCookiesString(){
        $this->cookies = [];

        if ( file_exists($this->cookie_path) )
            foreach (file($this->cookie_path) as $line) {
                if ($cookie = $this->parseCookieLine($line)) {
                    $this->cookies[$cookie['name']] = $cookie['value'];
                }
            }
        $this->cookies['COOKIECAV'] = $this->get_COOKIECAV();
        $this->cookies['ASP.NET_SessionId'] = $this->get_ASPSESSION();
        $this->cookies['assinadoc_cert_type'] = 'A1';
        $cookiesString = "";
        foreach ($this->cookies as $key => $value){
            $cookiesString .= "{$key}={$value};";
        }
        return "Cookie: {$cookiesString}";
    }



    function parseCookieLine($line)
    {
        // detect http only cookies and remove #HttpOnly prefix
        $httpOnly = $this->isHttp($line);

        if ($httpOnly) {
            $line = substr($line, 10);
        }

        if (!$this->isValidLine($line)) {
            return false;
        }

        $data = $this->getCookieData($line);

        $data['httponly'] = $httpOnly;

        return $data;
    }

    function getCookieData($line)
    {
        // execGet tokens in an array
        $data = explode("\t", $line);
        // trim the tokens
        $data =  array_map('trim', $data);

        return [
            'domain'     => $data[0],
            'flag'       => $data[1],
            'path'       => $data[2],
            'secure'     => $data[3],
            'name'       => $data[5],
            'value'      => $data[6],
            'expiration' => date('Y-m-d h:i:s', $data[4]),
        ];
    }

    function isValidLine($line)
    {
        return strlen($line) > 0 && $line[0] != '#' && substr_count($line, "\t") == 6;
    }

    function isHttp($line)
    {
        return substr($line, 0, 10) == '#HttpOnly_';
    }

   public  function get_captcha_response(){
        $url = "https://api.anti-captcha.com/createTask";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = <<<DATA
{
    "clientKey":"c9e702bf86537afe6e9a92d3c092b1bd",
    "task":
        {
            "type":"HCaptchaTaskProxyless",
            "websiteURL":"https://cav.receita.fazenda.gov.br",
            "websiteKey":"903db64c-2422-4230-a22e-5645634d893f"
        }
}
DATA;

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($resp, true);

        $taskId = $data['taskId'];

        $tentativas = 0;
        while (true){
            $url = "https://api.anti-captcha.com/getTaskResult";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $data = <<<DATA
{
    "clientKey":"c9e702bf86537afe6e9a92d3c092b1bd",
    "taskId":$taskId
}
DATA;

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            $resp = json_decode($resp, true);
            curl_close($curl);

            if (isset($resp['solution'])) return $resp['solution']['gRecaptchaResponse'];
            $tentativas= $tentativas + 1;
            if ($tentativas > 20)
                break;
        }
    }



    public  function get_captcha_response3(){
        $url = "https://api.capmonster.cloud/createTask";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Accept: application/json",
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = <<<DATA
{
    "clientKey":"982d8424bf02eb145cc2341b8e26d138",
    "task":
    {
        "type":"HCaptchaTask",
        "websiteURL":"https://cav.receita.fazenda.gov.br",
        "websiteKey":"48378d4b-eb31-409e-904d-e0c3f0aaa655",
        "proxyType":"http",
        "proxyAddress":"http://zproxy.lum-superproxy.io",
        "proxyPort":22225,
        "proxyLogin":"lum-customer-c_aeeb4574-zone-residential-country-br-session",
        "proxyPassword":"1*dATaVeR1pR0xY*_1",
        "userAgent":"Mozilla/5.0 (Linux; Android 6.0.1; Moto G (4)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36"
    }
}
DATA;

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($resp, true);

        $taskId = $data['taskId'];

        $tentativas = 0;
        while (true){
            $url = "https://api.capmonster.cloud/getTaskResult";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $data = <<<DATA
{
    "clientKey":"d7f71504acfa33ee64ca20d375182288",
    "taskId":$taskId
}
DATA;

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            $resp = json_decode($resp, true);
            curl_close($curl);

            if (isset($resp['solution'])) return $resp['solution']['gRecaptchaResponse'];
            $tentativas= $tentativas + 1;
            if ($tentativas > 20)
                break;
        }
    }



    public  function get_captcha_response2(){
        $username = 'lum-customer-c_aeeb4574-zone-residential';
        $password = '1*dATaVeR1pR0xY*_1';
        $port = 22225;
        $session = mt_rand();
        $super_proxy = 'zproxy.lum-superproxy.io';
        $proxy = "$username-country-br-session-$session:$password@http://$super_proxy:$port";
        
        $url = "http://2captcha.com/in.php?key=68b45b39b252051c9b067d1330817066&method=hcaptcha&sitekey=903db64c-2422-4230-a22e-5645634d893f&pageurl=https://cav.receita.fazenda.gov.br&json=1&proxy=$proxy";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

//for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($resp, true);

        $taskId = $data['request'];

        $tentativas = 0;
        sleep(15);
        while (true){
            $url = "http://2captcha.com/res.php?key=68b45b39b252051c9b067d1330817066&action=get&id={$taskId}&json=1";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            $resp = json_decode($resp, true);
            curl_close($curl);

            if ($resp['status'] ==1) return $resp['request'];
            $tentativas= $tentativas + 1;
            if ($tentativas > 20)
                return false;
            sleep(3);
        }
    }

    public function trocar_perfil($cnpj){
        $url = "https://cav.receita.fazenda.gov.br/autenticacao/api/mudarpapel/procuradorpj";
        $headers = array(
            "Connection: keep-alive",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
            "Accept: */*",
            "Content-Type: application/x-www-form-urlencoded",
            "X-Requested-With: XMLHttpRequest",
            "sec-ch-ua-mobile: ?0",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
            'sec-ch-ua-platform: "Windows"',
            "Origin: https://cav.receita.fazenda.gov.br",
            "Sec-Fetch-Site: same-origin",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Dest: empty",
            "Referer: https://cav.receita.fazenda.gov.br/ecac/Default.aspx",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );

        $cookie_cav_ex = $this->get_COOKIECAV();
        $asp_session_ex = $this->get_ASPSESSION();

        $url_quebra = 'http://216.238.75.208/trocar-perfil?cookiecav='.$cookie_cav_ex.'&aspsession='.$asp_session_ex.'&cnpj='.$cnpj; 
        // $token = $this->quebrar_captcha_pupter($url_quebra);


        $token = 'aa';
        // echo "token-------".$token;
        

        // if (!$token)
        //     $token = $this->get_captcha_response();

        // if (!$token)
        //     return false;
        $data = "ni=$cnpj&hCaptchaResponse=$token";
        $response=$this->exec($url, $headers, $data);
        echo $response;

        $response_json = json_decode($response);
        if(isset($response_json->Value) && strpos($response_json->Value, 'Não existe procuração eletrônica') !== false)
            return false;
        $this->setar_numero_documento_procuracao($cnpj);
        $url = 'https://cav.receita.fazenda.gov.br/ecac/';
        $page = $this->exec($url, array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
            "sec-ch-ua-mobile: ?0",
            'sec-ch-ua-platform: "Windows"',
            "Upgrade-Insecure-Requests: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "Sec-Fetch-Site: cross-site",
            "Sec-Fetch-Mode: navigate",
            "Sec-Fetch-User: ?1",
            "Sec-Fetch-Dest: document",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        ));

        $html = new Simple_html_dom();
        $html->load($page);
        if ($html->find('div[id=informacao-perfil]', 0) == null) {
            sleep(10);
            return false;
        }
        $retorno = $html->find('div[id=informacao-perfil]', 0)->find('div', 1);
        $texto_encontrado = trim($retorno->plaintext) ;

        $nao_encontrou_texto_procuracao = strpos($texto_encontrado, 'Procurador') === false;

        if ($nao_encontrou_texto_procuracao){
            return false;
        }

        $cnpj_na_pagina = substr($this->apenas_numero($texto_encontrado),0, 14);
        $cnpj_atual = $this->apenas_numero($cnpj);

        if ($cnpj_na_pagina !== $cnpj_atual){
            return false;
        }

        return $response;
    }

    public function quebrar_captcha_pupter($url){
        $curl = curl_init($url);


        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agents[rand(0, count($this->user_agents)-1 )]);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        if (curl_errno($curl))
            echo curl_error($curl);
        curl_close($curl);

        return $this->converterCaracterEspecial($response);

    }

    public function exec($url , $headers = [], $post_data='', $is_post=false, $cookie_extra='' )
    {
        $curl = curl_init($url);

        $this->setProxy($curl);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agents[rand(0, count($this->user_agents)-1 )]);

        $cookies = $this->getCookiesString();
        if(!empty($cookie_extra))
            $cookies.=$cookie_extra;

        $headers[] =  $cookies;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if($is_post)
            curl_setopt($curl, CURLOPT_POST, 1);
        if ($post_data != '')
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        if (curl_errno($curl))
            echo curl_error($curl);
        curl_close($curl);

        return $this->converterCaracterEspecial($response);
    }


    public function obter_pdf($url, $filename, $headers = [], $post_data='', $is_post=false )
    {
        $response = $this->exec($url, $headers, $post_data, $is_post);
        $caminho_local = $filename;

        $aux_dir_ext = str_replace(FCPATH, "",$caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        if ( $this->verifica_pdf_valido($response)){
            upload_google_source($response, $aux_dir_ext);
            return "https://storage.googleapis.com/cron-veri-files-br/".$aux_dir_ext;
        }
        return "";

    }

    function verifica_pdf_valido($content){
        if (preg_match("/^%PDF-1./", $content)) {
            return true;
        } else {
            return false;
        }
    }

    public function converterCaracterEspecial($text){
        return html_entity_decode($text, ENT_QUOTES, "utf-8");
    }

    public function encerrar_conection($curl){
        if ( ! is_null( $curl ) ){
            curl_close( $curl );
            $curl = null;
        }
    }

    public function obter_numero_documento(){
        if ($this->numero_documento_procuracao)
            return $this->numero_documento_procuracao;
        else
            return $this->numero_documento_certificado;
    }

    public function obter_numero_documento_certificado(){
        return $this->numero_documento_certificado;
    }
    public function setar_numero_documento_certificado($numero){
        return $this->numero_documento_certificado = $numero;
    }

    public function obter_numero_documento_procuracao(){
        return $this->numero_documento_procuracao;
    }
    public function setar_numero_documento_procuracao($numero){
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
        $headers = array(
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
           "sec-ch-ua-mobile: ?0",
           'sec-ch-ua-platform: "Windows"',
           "Upgrade-Insecure-Requests: 1",
           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
           "Sec-Fetch-Site: cross-site",
           "Sec-Fetch-Mode: navigate",
           "Sec-Fetch-User: ?1",
           "Sec-Fetch-Dest: document",
           "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
        );
        $url = "https://cav.receita.fazenda.gov.br/ecac/Default.aspx";
        $response=$this->exec($url, $headers);
        $html = new Simple_html_dom();
        $html->load($response);

        $div_sessao = $html->find('div[id=informacao-perfil]', 0);
        echo $div_sessao;
        if(empty($div_sessao)){
            echo 'Sessao expirada';
            return false;
        }
        return true;
    }

    public function apenas_numero($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

    function gera_cookie($curl){
        curl_setopt($curl, CURLOPT_URL , 'https://www2.cav.receita.fazenda.gov.br/Servicos/ATSPO/eSitFis.app/IdentificaUsuario/index.asp' );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $result = curl_exec($curl);
        if(curl_errno($curl))
            echo curl_error($curl);
    }

    public function get_COOKIECAV(){
        return $this->cookiecav;

    }

    public function get_ASPSESSION(){
        return $this->aspsession;
    }

    public function set_numero_documento_procuracao($cnpj){
        $this->numero_documento_procuracao=$cnpj;
    }
}
