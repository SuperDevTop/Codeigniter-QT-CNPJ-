<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'libraries/Ecac/Ecac.php');


class Procuracao extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    public function get_procuracoes(){
        $this->encerrar_conection(null);
        $response = shell_exec("python {$this->path_script_procuracao} \"{$this->cookie_path}\" ");
        $json  = json_decode($response, TRUE);
        return $json;
    }

}