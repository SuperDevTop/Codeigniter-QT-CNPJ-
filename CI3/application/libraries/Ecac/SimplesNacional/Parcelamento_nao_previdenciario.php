<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');

class Parcelamento_nao_previdenciario extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    /**
     * path_script_buscar_parcela_nao_previdenciario
     *
     * CAMINHO PARA O SCRIPT divida ativa nao previdenciaria
     *
     * @var string
     */
    protected $path_script_buscar_parcela_nao_previdenciario = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'buscar_parcelamentos_nao_previdenciario.py';

    public function processos_negociados()
    {
        $response = shell_exec("python3.9 {$this->path_script_buscar_parcela_nao_previdenciario} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" ");
        return json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }
    
    
    //GERAR PARCELA
    /**
     * path_script_gerar_parcela_nao_previdenciario
     *
     * CAMINHO PARA O SCRIPT divida ativa nao previdenciaria
     *
     * @var string
     */
    protected $path_script_gerar_parcela_nao_previdenciario = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'gerar_parcela_nao_previdenciario.py';
    
    function gerar_parcela_nao_previdenciario($numero_processo, $nome_tributo, $data_vencimento, $valor_parcela, $numero_parcela){
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $caminho_local = $this->caminho_da_pasta_pdfs . "DARF-{$nome_tributo}-{$data_atual}.pdf";
        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);

        $response = shell_exec("python3.9 {$this->path_script_gerar_parcela_nao_previdenciario} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$numero_processo}\" \"{$nome_tributo}\" \"{$data_vencimento}\" \"{$valor_parcela}\" \"{$numero_parcela}\" \"{$aux_dir_ext}\"");
        
        return $response;
    }
}