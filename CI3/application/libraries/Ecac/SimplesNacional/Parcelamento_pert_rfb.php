<?php

use GuzzleHttp\Psr7\Response;

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Ecac/Ecac.php');


class Parcelamento_pert_rfb extends Ecac
{
    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

     /**
     * path_script_buscar_parcela_pert_rfb
     *
     * CAMINHO PARA O SCRIPT divida ativa nao previdenciaria
     *
     * @var string
     */
    protected $path_script_buscar_parcela_pert_rfb = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'buscar_parcelamentos_pert_rfb.py';

    public function obter_parcelamentos($cnpj)
    {
        $response = shell_exec("python3.9 {$this->path_script_buscar_parcela_pert_rfb} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$cnpj}\" ");
        return json_decode($response);
    }

    //GERAR PARCELA

    /**
     * path_script_gerar_parcela_pert_rfb
     *
     * CAMINHO PARA O SCRIPT divida ativa nao previdenciaria
     *
     * @var string
     */
    protected $path_script_gerar_parcela_pert_rfb = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'gerar_parcela_pert_rfb.py';
    
    function gerar_parcela_pert_rfb($id_formatado, $id_parcela){
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $caminho_local = $this->caminho_da_pasta_pdfs . "DARF-PERT-RFB-{$data_atual}.pdf";
        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);

        $retorno = shell_exec("python3.9 {$this->path_script_gerar_parcela_pert_rfb} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$id_formatado}\" \"{$id_parcela}\" \"{$aux_dir_ext}\" ");

        return $retorno;
    }
}
