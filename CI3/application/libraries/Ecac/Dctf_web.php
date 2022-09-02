<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Ecac/Ecac.php');

class Dctf_web extends Ecac
{

    public function __construct($params = array(), $conectar = true)
    {
        parent::__construct($params, $conectar);
    }

    /**
     * path_script_buscar_dctf_web
     *
     * CAMINHO PARA O SCRIPT
     *
     * @var string
     */
    protected $path_script_buscar_dctf_web = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'buscar_dctf_web.py';

    public function buscar_declaracoes()
    {
        $certi = $this->public_key;

        // echo "python {$this->path_script_buscar_dctf_web} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$certi}\" ";
        // die();
        $response = shell_exec("python {$this->path_script_buscar_dctf_web} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$certi}\" ");

        // echo $response;

        return json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
    }


    //GERAR PARCELA
    /**
     * path_script_gerar_darf
     *
     * CAMINHO PARA O SCRIPT
     *
     * @var string
     */
    protected $path_script_gerar_darf = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'gerar_dctf_web_darf.py';

    function gerar_darf($id_declaracao, $id_controle)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $caminho_local = $this->caminho_da_pasta_pdfs . "DARF-{$data_atual}.pdf";
        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        $response = shell_exec("python {$this->path_script_gerar_darf} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$aux_dir_ext}\" \"{$id_declaracao}\" \"{$id_controle}\"");

        return $response;
    }

    //GERAR RECIBO
    /**
     * path_script_gerar_recibo
     *
     * CAMINHO PARA O SCRIPT
     *
     * @var string
     */
    protected $path_script_gerar_recibo = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'gerar_dctf_web_recibo.py';

    function gerar_recibo($id_declaracao, $id_controle)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $caminho_local = $this->caminho_da_pasta_pdfs . "RECIBO-{$data_atual}.pdf";
        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        $response = shell_exec("python {$this->path_script_gerar_recibo} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$aux_dir_ext}\" \"{$id_declaracao}\" \"{$id_controle}\"");

        return $response;
    }

    //GERAR EXTRATO
    /**
     * path_script_gerar_extrato
     *
     * CAMINHO PARA O SCRIPT
     *
     * @var string
     */
    protected $path_script_gerar_extrato = SCRIPTSPATH . DIRECTORY_SEPARATOR . 'gerar_dctf_web_extrato.py';

    function gerar_extrato($id_declaracao, $id_controle)
    {
        date_default_timezone_set('America/Bahia');
        $data_atual = date('Ymdhis');

        $caminho_local = $this->caminho_da_pasta_pdfs . "EXTRATO-{$data_atual}.pdf";
        $aux_dir_ext = str_replace(FCPATH, "", $caminho_local);
        $aux_dir_ext = str_replace("//", "/", $aux_dir_ext);
        $response = shell_exec("python {$this->path_script_gerar_extrato} \"{$this->get_COOKIECAV()}\" \"{$this->get_ASPSESSION()}\" \"{$aux_dir_ext}\" \"{$id_declaracao}\" \"{$id_controle}\"");

        return $response;
    }
}
