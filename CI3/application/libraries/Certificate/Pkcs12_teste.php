<?php

require_once(APPPATH.'libraries/Certificate/Asn.php');

class Pkcs12_teste
{
    /**
     * Path para o diretorio onde o arquivo pfx está localizado
     *
     * @var string
     */
    public $pathCerts = '';
    
    /**
     * Path para o arquivo pfx (certificado digital em formato de transporte)
     *
     * @var string
     */
    public $pfxFileName = '';
    
    /**
     * Conteudo do arquivo pfx
     *
     * @var string
     */
    public $pfxCert = '';
    
    /**
     * Numero do CNPJ do emitente
     *
     * @var string
     */
    public $cnpj = '';
    
    /**
     * String que contêm a chave publica em formato PEM
     *
     * @var string
     */
    public $pubKey = '';
    
    /**
     * String quem contêm a chave privada em formato PEM
     *
     * @var string
     */
    public $priKey = '';
    
    /**
     * String que conten a combinação da chave publica e privada em formato PEM
     * e a cadeida completa de certificação caso exista
     *
     * @var string
     */
    public $certKey = '';
    
    /**
     * Flag para ignorar testes de validade do certificado
     * isso é usado apenas para fins de testes
     *
     * @var boolean
     */
    public $ignoreValidCert = false;
    
    /**
     * Path para a chave publica em arquivo
     *
     * @var string
     */
    public $pubKeyFile = '';
    
    /**
     * Path para a chave privada em arquivo
     *
     * @var string
     */
    public $priKeyFile = '';
    
    /**
     * Path para o certificado em arquivo
     *
     * @var string
     */
    public $certKeyFile = '';
    
    /**
     * Timestamp da data de validade do certificado
     *
     * @var float
     */
    public $expireTimestamp = 0;
    
    /**
     * Mensagem de erro da classe
     *
     * @var string
     */
    public $error = '';
    
    /**
     * Id do docimento sendo assinado
     *
     * @var string
     */
    public $docId = '';

    /**
     * Método de construção da classe
     *
     * @param string  $pathCerts       Path para a pasta que contêm os certificados digitais
     * @param string  $cnpj            CNPJ do emitente, sem  ./-, apenas os numeros
     * @param string  $pubKey          Chave publica em formato PEM, não o path mas a chave em si
     * @param string  $priKey          Chave privada em formato PEM, não o path mas a chave em si
     * @param string  $certKey         Certificado em formato PEM, não o path mas a chave em si
     * @param bool    $ignoreValidCert
     * @param boolean $ignoreValidCert Ignora a validade do certificado, mais usado para fins de teste
     */
    public function __construct(
        $pathCerts = '',
        $cnpj = '',
        $pubKey = '',
        $priKey = '',
        $certKey = '',
        $ignoreValidCert = false
    ) {
        $ncnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (empty($pathCerts)) {
            //estabelecer diretorio default
            $pathCerts = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'certs'.DIRECTORY_SEPARATOR;
        }
        if (! empty($pathCerts)) {
            if (!is_dir(trim($pathCerts))) {
				echo "Um path válido para os certificados deve ser passado."
                    . " Diretório [$pathCerts] não foi localizado."
                ;
				echo "\n";

			}
            $this->pathCerts = trim($pathCerts);
        }
        $this->ignoreValidCert = $ignoreValidCert;
        $flagCert = false;
        if ($pubKey != '' && $priKey != '' && strlen($pubKey) > 500 && strlen($priKey) > 500) {
            $this->pubKey = $pubKey;
            $this->priKey = $priKey;
            $this->certKey = $priKey."\r\n".$pubKey;
            $flagCert = true;
        }
        if ($certKey != '') {
            $this->certKey = $certKey;
        }
        $this->cnpj = $ncnpj;
        if (! $this->zInit($flagCert)) {
            echo $this->error;
        }
    }
    
    /**
     * zInit
     * Método de inicialização da classe irá verificar
     * os parâmetros, arquivos e validade dos mesmos
     * Em caso de erro o motivo da falha será indicada na parâmetro
     * error da classe, os outros parâmetros serão limpos e os
     * arquivos inválidos serão removidos da pasta
     *
     * @param  boolean $flagCert indica que as chaves já foram passas como strings
     * @return boolean
     */
    private function zInit($flagCert = false)
    {
        //se as chaves foram passadas na forma de strings então verificar a validade
        if ($flagCert) {
            //já que o certificado existe, verificar seu prazo de validade
            //o certificado será removido se estiver vencido
            if (!$this->ignoreValidCert) {
                return $this->zValidCerts($this->pubKey);
            }
        } else {
            if (substr($this->pathCerts, -1) !== DIRECTORY_SEPARATOR) {
                $this->pathCerts .= DIRECTORY_SEPARATOR;
            }
            //monta o path completo com o nome da chave privada
            $this->priKeyFile = $this->pathCerts.$this->cnpj.'_priKEY.pem';
            //monta o path completo com o nome da chave publica
            $this->pubKeyFile =  $this->pathCerts.$this->cnpj.'_pubKEY.pem';
            //monta o path completo com o nome do certificado (chave publica e privada) em formato pem
            $this->certKeyFile = $this->pathCerts.$this->cnpj.'_certKEY.pem';
            //se as chaves não foram passadas em strings, verifica se os certificados existem
            if (is_file($this->priKeyFile) && is_file($this->pubKeyFile) && is_file($this->certKeyFile)) {
                //se as chaves existem deve ser verificado sua validade
                $this->pubKey = file_get_contents($this->pubKeyFile);
                $this->priKey = file_get_contents($this->priKeyFile);
                $this->certKey = file_get_contents($this->certKeyFile);
                //já que o certificado existe, verificar seu prazo de validade
                if (! $this->ignoreValidCert) {
                    return $this->zValidCerts($this->pubKey);
                }
            }
        }
        return true;
    }//fim init

    /**
     * loadPfxFile
     *
     * @param  string $pathPfx        caminho completo para o arquivo pfx
     * @param  string $password       senha para abrir o certificado pfx
     * @return bool
     */
    public function loadPfxFile(
        $pathPfx = '',
        $password = ''
    ) {
        if (! is_file($pathPfx)) {
		   echo "O nome do arquivo PFX deve ser passado. Não foi localizado o arquivo [$pathPfx].";
        }
        $this->pfxCert = file_get_contents($pathPfx);
        return $this->loadPfx($this->pfxCert, $password);
    }

    /**
     * loadPfx
     * Carrega um novo certificado no formato PFX
     * Isso deverá ocorrer a cada atualização do certificado digital, ou seja,
     * pelo menos uma vez por ano, uma vez que a validade do certificado
     * é anual.
     * Será verificado também se o certificado pertence realmente ao CNPJ
     * Essa verificação checa apenas se o certificado pertence a matriz ou filial
     * comparando apenas os primeiros 8 digitos do CNPJ, dessa forma ambas a
     * matriz e as filiais poderão usar o mesmo certificado indicado na instanciação
     * da classe, se não for um erro irá ocorrer e
     * o certificado não será convertido para o formato PEM.
     * Em caso de erros, será retornado false e o motivo será indicado no
     * parâmetro error da classe.
     * Os certificados serão armazenados como <CNPJ>-<tipo>.pem
     *
     * @param  string  $pfxContent     arquivo PFX
     * @param  string  $password       Senha de acesso ao certificado PFX
     * @return bool
     */
    public function loadPfx(
        $pfxContent = '',
        $password = ''
    ) {
        if ($password == '') {
            echo "A senha de acesso para o certificado pfx não pode ser vazia.";
            echo "\n";
        }
        //carrega os certificados e chaves para um array denominado $x509certdata
        $x509certdata = array();

        if (!openssl_pkcs12_read($pfxContent, $x509certdata, $password)) {
            echo "O certificado não pode ser lido!! Senha errada ou arquivo corrompido ou formato inválido!!";
			echo "\n";
			return;
		}
        $this->pfxCert = $pfxContent;

		$this->cnpj = Asn::getCNPJCert($x509certdata['cert']) != '' ? Asn::getCNPJCert($x509certdata['cert']) : Asn::getCPFCert($x509certdata['cert']) ;

        //monta o path completo com o nome da chave privada
        $this->priKeyFile = $this->pathCerts.$this->cnpj.'_priKEY.pem';
        //monta o path completo com o nome da chave publica
        $this->pubKeyFile =  $this->pathCerts.$this->cnpj.'_pubKEY.pem';
        //monta o path completo com o nome do certificado (chave publica e privada) em formato pem
        $this->certKeyFile = $this->pathCerts.$this->cnpj.'_certKEY.pem';


		//verifica sua data de validade
		if (! $this->zValidCerts($x509certdata['cert'])) {
			echo 'CNPJ do certificado  ' . $this->cnpj;
			echo "\n";
			echo $this->error;
			echo "\n";
		}
		$this->zSavePemFiles($x509certdata);

		$this->pubKey=$x509certdata['cert'];
        $this->priKey=$x509certdata['pkey'];
        $this->certKey=$x509certdata['pkey']."\r\n".$x509certdata['cert'];
        return true;
    }
    
    /**
     * zSavePemFiles
     *
     * @param  array $x509certdata
     */
    private function zSavePemFiles($x509certdata)
    {
        if (empty($this->pathCerts)) {
			echo "Não está definido o diretório para armazenar os certificados.";
			echo "\n";

		}
        if (! is_dir($this->pathCerts)) {
			echo "Não existe o diretório para armazenar os certificados.";
			echo "\n";

		}
        //recriar os arquivos pem com o arquivo pfx
        if (!file_put_contents($this->priKeyFile, $x509certdata['pkey'])) {
            echo "Falha de permissão de escrita na pasta dos certificados!!";
			echo "\n";


		}
        file_put_contents($this->pubKeyFile, $x509certdata['cert']);
        file_put_contents($this->certKeyFile, $x509certdata['pkey']."\r\n".$x509certdata['cert']);
    }
    
    /**
     * aadChain
     *
     * @param  array $aCerts Array com os caminhos completos para cada certificado da cadeia
     *                     ou um array com o conteúdo desses certificados
     * @return void
     */
    public function aadChain($aCerts = array())
    {
        $certificate = $this->certKey;
        foreach ($aCerts as $cert) {
            if (is_file($cert)) {
                $dados = file_get_contents($cert);
                $certificate .= "\r\n" . $dados;
            } else {
                $certificate .= "\r\n" . $cert;
            }
        }
        $this->certKey = $certificate;
        if (is_file($this->certKeyFile)) {
            file_put_contents($this->certKeyFile, $certificate);
        }
    }
    
    /**
     * zValidCerts
     * Verifica a data de validade do certificado digital
     * e compara com a data de hoje.
     * Caso o certificado tenha expirado o mesmo será removido das
     * pastas e o método irá retornar false.
     *
     * @param  string $pubKey chave publica
     * @return boolean
     */
    protected function zValidCerts($pubKey)
    {
        if (! $data = openssl_x509_read($pubKey)) {
                //o dado não é uma chave válida
                $this->zRemovePemFiles();
                $this->zLeaveParam();
                $this->error = "A chave passada está corrompida ou não é uma chave. Obtenha s chaves corretas!!";
                return false;
        }
        $certData = openssl_x509_parse($data);
        // reformata a data de validade;
        $ano = substr($certData['validTo'], 0, 2);
        $mes = substr($certData['validTo'], 2, 2);
        $dia = substr($certData['validTo'], 4, 2);
        //obtem o timestamp da data de validade do certificado
        $dValid = gmmktime(0, 0, 0, $mes, $dia, $ano);
        // obtem o timestamp da data de hoje
        $dHoje = gmmktime(0, 0, 0, date("m"), date("d"), date("Y"));
        // compara a data de validade com a data atual
        $this->expireTimestamp = $dValid;
        if ($dHoje > $dValid) {
            $this->zRemovePemFiles();
            $this->zLeaveParam();
            $msg = "Data de validade vencida! [Valido até $dia/$mes/$ano]";
            $this->error = $msg;
            return false;
        }
        return true;
    }
    

    /**
     * zRemovePemFiles
     * Apaga os arquivos PEM do diretório
     * Isso deve ser feito quando um novo certificado é carregado
     * ou quando a validade do certificado expirou.
     */
    private function zRemovePemFiles()
    {
        if (is_file($this->pubKeyFile)) {
            unlink($this->pubKeyFile);
        }
        if (is_file($this->priKeyFile)) {
            unlink($this->priKeyFile);
        }
        if (is_file($this->certKeyFile)) {
            unlink($this->certKeyFile);
        }
    }
    
    /**
     * zLeaveParam
     * Limpa os parametros da classe
     */
    private function zLeaveParam()
    {
        $this->pfxCert='';
        $this->pubKey='';
        $this->priKey='';
        $this->certKey='';
        $this->pubKeyFile='';
        $this->priKeyFile='';
        $this->certKeyFile='';
        $this->expireTimestamp='';
    }

}
