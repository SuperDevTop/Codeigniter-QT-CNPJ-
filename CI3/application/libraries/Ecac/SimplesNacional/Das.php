<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Das extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function get_das($cnpj='', $ano=''){
        $this->encerrar_conection();

        if ($ano == '')
            $ano = '2021';
        if ($cnpj != '')
            $this->setar_numero_documento_procuracao($cnpj);

        $response = shell_exec("python3.9 {$this->path_script_simples_nacional}  \"{$this->cookie_path}\" \"{$cnpj}\" \"{$this->caminho_da_pasta_pdfs}\" \"{$ano}\"");
        echo var_export(json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true));

        return json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }

    public function get_das_debitos($cnpj=''){
        $this->encerrar_conection();
        if ($cnpj != '')
         $this->setar_numero_documento_procuracao($cnpj);
        $response = shell_exec("python3.9 {$this->path_script_simples_nacional_debitos}  \"{$this->cookie_path}\" \"{$cnpj}\"");
        return json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }

}