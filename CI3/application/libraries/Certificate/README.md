Neste diretório se encontram um grupo de classes e arquivos para o tratamento e uso de Certificados Digitais modelo A1 (pkcs12) .
=========
<b>Para o uso destas classe é impressindível que esteja instalado e ativo o módulo do OpenSSL do PHP.</b>

Class Pkcs12
==========
Esta é a classe principal para o uso dos certificados digitais, e provê os métodos principais para converter os certificados pfx em PEM (para uso do PHP), realizar as assinaturas digitais dos xml e fazer as verificações e validações dos certificados e das assinaturas digitais. 

Métodos Públicos
==========

\__construct()
--------
Método contrutor da classe - ao instanciar a classe a mesma executa algumas configurações básicas.
 
```php
use Common\Certificate\Pkcs12;

$oCertificate = new Pkcs12(string $pathCerts,string $cnpj[,string $pubKey[,string $priKey[,string $certKey,boolean $ignoreValidCert]]])
```                                               
Parâmetros
--------
<b>pathCerts</b>

string com o caminho completo para o diretorio que contem os certificados digitais. Nesse diretorio serão colocados os arquivos .pfx, e .pem
Caso não seja passado um caminho válido para esse parametro um Exception será retornado. Caso seja passado uma string vazia o diretorio "certs" da API será utilizado como padrão.
Esse path deve ser passado como path real. NOTA: Evite passar caminhos relativos. 

<b>cnpj</b>

String com o Número do CNPJ do proprietário do certificado digital, apenas 14 digitos numéricos, sem símbolos ou espaços.
Será retornado um Exception caso o CNPJ não tenha exatos 14 digitos numéricos. 

<b>pubKey</b>

Opcional string com o conteúdo da chave publica em formato PEM. Esse conteúdo, ou seja a chave propriamente dita, pode ser armazenada em banco de dados e passada como parâmetro na API, não havendo necessidade de ficar armazenada como um arquivo.  
 
<b>priKey</b>

Opcional string com o conteúdo da chave privada em formato PEM. Esse conteúdo, ou seja a chave propriamente dita, pode ser armazenada em banco de dados e passada como parâmetro na API, não havendo necessidade de ficar armazenada como um arquivo.

<b>certKey</b>

Opcional string com o conteúdo do certificado em formato PEM, que é composto das chaves publica e privada, além da cadeia de certificação (se houver). Caso não seja passada como parâmetro esse certificado será recriado se as chaves publica e privada o forem.

<b>ignoreValidCert</b>

Opcional boolean DEFAULT false, manda a classe ignorar as validações do certificado, tanto do seu proprietário como sua data de validade.


loadPfxFile
--------
Este método permite o carregamento de um novo arquivo PFX (certificado em formato de transporte) na API através de um arquivo.                                                       
```php 
boolean $oCertificate->loadPfxFile(string $pathPfx,string $password,[boolean $createFiles,[boolean $ignoreValidity,[boolean $ignoreOwner]]])
```
Parâmetros
--------
<b>pathPfx</b>

string esse é o path para o arquivo pfx

<b>password</b>

string senha de decriptação do arquivo pfx

<b>createFiles</b>

Opcional DEFAULT true - indica se devem ser criados os arquivos PEM na pasta indicada na construção da classe

<b>ignoreValidity</b>

Opcional boolean DEFAULT false, manda a classe ignorar a data de validade do certificado.

<b>ignoreOwner</b>

Opcional boolean DEFAULT false, manda a classe ignorar o cnpj do proprietário do certificado.


loadPfx
--------
Este método permite carregar um certificado no formato pfx através de seu conteúdo e não através de um path.    
```php
boolean $oCertificate->loadPfx($pfxContent,$password,[boolean $createFiles,[boolean $ignoreValidity,[boolean $ignoreOwner]]])
```
Parâmetros
--------

<b>pfxContent</b>

string conteúdo do arquivo pfx, esse conteudo pode estar armazenado por exemplo em banco de dados 

<b>password</b>

string senha de decriptação do arquivo pfx

<b>createFiles</b>

Opcional DEFAULT true - indica se devem ser criados os arquivos PEM na pasta indicada na construção da classe

<b>ignoreValidity</b>

Opcional boolean DEFAULT false, manda a classe ignorar a data de validade do certificado.

<b>ignoreOwner</b>

Opcional boolean DEFAULT false, manda a classe ignorar o cnpj do proprietário do certificado.


aadChain
--------
Este método permite a adição da cadeia de certificação ao certificado digital. Alguns estados tem feito alterações em seus sistemas de forma a exigir que a cadeia completa de certificação até a RAIZ brasileira esteja contida dentro do certificado usado na comunicação SOAP.
```php
void $oCertificate->aadChain(array $aCerts)
```

Parâmetros
--------
<b>aCerts</b>

array com os paths ou os conteúdos dos certificados da cadeia de certificação. 

Class Asn
==========

Esta é uma classe também relevante, mas é apenas um complemento da classe anterior e é usada unicamente para extrair o numero do CNPJ do proprietário do certificado.
Esta classe é usada para verificar se o usuáio é realmente o proprietário do certificado pois caso não seja a assinatura e a conexão com a SEFAZ será rejeitada. 

Métodos Públicos
=========

getCNPJCert
--------
Este método extrai do interior do certificado digital A1, a identificação do CNPJ do proprietário.
```php
use Common\Certificate\Asn;

string Asn::getCNPJCert($certPem)
```
Parâmetros
--------
<b>certPem</b>

string com o conteúdo do certificado digital em formato PEM (normalmente a chave pública)

<i>NOTA: As demais classes e arquivos deste diretório são complementos auxiliares destas duas classes principais e não possuem metodos publicos a serem utilizados.</i>



