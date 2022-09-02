<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron_unico extends CI_Controller
{
    // Situacao_fiscal_ecac_procuracao/cron_situacao_fiscal_com_procuracao/".$nomeBanco;
    // Situacao_cadin_ecac_procuracao/cron_pendencia_cadin_com_procuracao/".$nomeBanco;
    // Mensagens_ecac_procuracao/buscar_ecac_com_procuracao/".$nomeBanco;
    // Das_ecac_procuracao/cron_das/".$nomeBanco;
    // Dctf_ecac_procuracao/cron_dctf/".$nomeBanco;
    // vencimento_procuracao/cron_procuracao/".$nomeBanco;
    // eprocessos_procuracao/buscar/".$nomeBanco;

    // Cron_simplesnacional_pedidos_parcelamento_procuracao/buscar/".$nomeBanco;
    // Simplesnacional_parcela_procuracao/buscar_parcelas/".$nomeBanco;
    // Cron_pert_pedidos_parcelamento_procuracao/buscar/".$nomeBanco;
    // Parcelamento_pert_procuracao/buscar_parcelas/".$nomeBanco;
    // Das_debitos_ecac_procuracao/cron_das_debitos/".$nomeBanco;
    // Cron_parcelamento_lei_12996_procuracao/buscar/".$nomeBanco;

    // Cron_parcelamento_nao_previdenciario_procuracao/buscar/".$nomeBanco;
    private $banco;
    private $params;

    private $myhashmap = array();


    function buscar_01()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_nao_atualizou_diagnostico($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
            $this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
            $this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_situacao_fiscal_ecac($item->cnpj); 
            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function buscar_02()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_2($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function buscar_03()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_3($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

function buscar_04()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_4($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_05()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_5($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_06()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_6($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_07()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_7($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_08()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_8($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_09()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_9($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_010()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_10($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_011()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_11($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_012()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_12($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_013()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_13($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_014()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_14($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_015()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_15($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_016()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_16($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_017()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_17($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_018()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_18($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_019()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_19($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_020()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_20($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_21()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_21($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_22()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_22($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_23()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_23($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_24()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_24($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_25()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_25($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_26()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_26($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_27()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_27($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_28()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_28($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_29()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_29($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_30()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_30($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_31()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_31($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_32()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_32($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_33()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_33($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_34()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_34($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_35()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_35($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_36()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_36($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_37()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_37($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_38()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_38($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_39()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_39($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_40()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_40($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_41()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_41($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_42()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_42($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_43()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_43($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_44()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_44($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_45()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_45($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_46()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_46($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_47()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_47($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_48()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_48($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }
    function buscar_49()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_51($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function buscar_50()
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $this->uri->segment(3);

        $this->config->load('ecac_robo_config');

        $this->load->model('certificadocontador_model', 'certificado');
        $this->load->model('contadorprocuracao_model', 'contadorprocuracao');
        $this->load->model('ecac_sessao_model');
        $this->load->model('dctf_model');


        $this->load->helper('googlestorage_helper');

        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($this->banco);

        //*************************************************************** */
        $dctf_declarados = $this->dctf_model->find_all_dctf($this->banco);
        foreach ($dctf_declarados as $d) {
            $this->myhashmap[$d->cnpj . "/" . $d->periodo] = $d;
        }
        /**************************************************************** */
        foreach ($cerficados as $cerficado) {
            if(date('Ymd', $cerficado->data_validade) < date('Ymd')){
                echo 'Erro: Data de validade vencidade';
                continue;
            }
            /**
             * Carrega a library principal ecac_robo_library_procuracao
             */
            $this->params = array(
                'numero_documento_certificado' => $cerficado->cnpj_data,
                'certificado' => $cerficado,
            );

            // $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas($this->banco, $cerficado->id_contador);
            $empresas_com_procuracao = $this->contadorprocuracao->buscar_empresas_vinculadas_extra_1($this->banco, $cerficado->id_contador);

            if (empty($empresas_com_procuracao)) {
                continue;
            }

            $this->load->library('Ecac/Ecac', $this->params, 'ecac_robo_library_procuracao');
            $this->params['start_sessao']=false;
$this->params['cookiecav']=$this->ecac_robo_library_procuracao->get_COOKIECAV();
$this->params['aspsession']=$this->ecac_robo_library_procuracao->get_ASPSESSION();
            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if (!$this->ecac_robo_library_procuracao->acesso_valido()) {
                echo 'ERRO: Sessao expirada';
                unset($this->ecac_robo_library_procuracao);
                continue;
            }

            foreach ($empresas_com_procuracao as $item) {
                echo "<br>Buscando dados da empresa: $item->cnpj <br>";
                $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($item->cnpj);


                $validado = $this->ecac_robo_library_procuracao->trocar_perfil($item->cnpj);
                if (!$validado) {
                    echo "CNPJ: {$item->cnpj} - sem procuração";
                    $this->contadorprocuracao->insere_empresas_sem_procuracao($this->banco, $item->cnpj);
                    continue;
                }


                $this->buscar_mensagens_ecac($item->cnpj);

                $this->buscar_das_ecac_procuracao($item->cnpj);

                // $this->buscar_vencimento_procuracao($cerficado);

                $this->buscar_eprocessos_procuracao($item->cnpj);
                $this->buscar_dctf_ecac_procuracao($item->cnpj);

                $this->buscar_situacao_fiscal_ecac($item->cnpj);

                 $this->buscar_situacao_cadin($item->cnpj);
 $this->buscar_simplesnacional_pedidos_parcelamento($item->cnpj);
 $this->buscar_simplesnacional_parcelas($item->cnpj);
 $this->buscar_pert_pedidos_parcelamento($item->cnpj);
 $this->buscar_pert_parcelas($item->cnpj);


            }

            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    private function buscar_situacao_fiscal_ecac($cnpj)
    {

        $folder_pdf = FCPATH . 'arquivos/pdf-certidao-ecac-sp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $params = $this->params;
        $params['caminho_da_pasta_pdfs'] =  $folder_pdf;

        $this->load->library('Ecac/SituacaoFiscalProcuracao', $params, 'ecac_robo_situacao_fiscal_ecac');
       $this->ecac_robo_situacao_fiscal_ecac->set_numero_documento_procuracao($cnpj);

        /**
         * Grava a situação fiscal e o pdf
         */
        $path_pdf = $this->ecac_robo_situacao_fiscal_ecac->baixar_pdf_situacao_fiscal();

        if (empty($path_pdf)) {
            echo "<br>Busca Situação fiscal ECAC - ERRO: pdf invalido - CNPJ: $cnpj";
            return;
        }

        $carregado = false;
        $pdf = new PdfToText();

        try {
            $pdf->LoadFromString(file_get_contents($path_pdf));
            $carregado = true;
        } catch (Exception $e) {
            return;
        }

        $texto_base = "Pendência -";
        $pos = strpos($pdf->Text, $texto_base);

        $possui_pendencia =  false;

        if ($pos !== false)
            $possui_pendencia = true;

        $caminho_download = $path_pdf;

        $existe_situacao = $this->verifica_se_existe_situacao($this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento(), $this->banco);
        if ($existe_situacao > 0) {
            $this->update_situacao_fiscal($possui_pendencia, '', $caminho_download, $this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento(), $this->banco);
        } else {
            $this->inserir_situacao_fiscal($possui_pendencia, '', $caminho_download, $this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento(), $this->banco);
        }

        echo "<br>==============SUCESSO NA OPERAÇÃO==========";
        echo "<br>";
        echo "<br>Documento: {$this->ecac_robo_situacao_fiscal_ecac->obter_numero_documento()}";
        echo "<br>";
        $mensagem_pendencia = $possui_pendencia ? "<br>Possui pendência." : "<br>Não foram encontradas pêndencias.";
        echo "<br>ituação Fiscal: {$mensagem_pendencia}";
        echo "<br>";
        echo "<br>PDF situação: {$caminho_download}";
        echo "<br>";
        echo "<br>===========================================";
        echo "<br>";
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection

        unset($this->ecac_robo_situacao_fiscal_ecac);
        echo "<br>Busca Situação fiscal ECAC concluído";
    }

    private function verifica_se_existe_situacao($cnpj_data, $banco)
    {
        $this->load->model('situacao_fiscal_model');
        $resultado = $this->situacao_fiscal_model->verifica_se_existe($cnpj_data, $banco);
        return $resultado->qtd;
    }

    private function inserir_situacao_fiscal($possui_pendencia, $path_pdf, $caminho_download, $cnpj_data, $banco)
    {
        $this->load->model('situacao_fiscal_model');
        $this->situacao_fiscal_model->insert($possui_pendencia, '', $caminho_download, $cnpj_data, $banco);
    }

    private function update_situacao_fiscal($possui_pendencia, $path_pdf, $caminho_download, $cnpj_data, $banco)
    {
        $this->load->model('situacao_fiscal_model');
        $this->situacao_fiscal_model->update($possui_pendencia, '', $caminho_download, $cnpj_data, $banco);
    }

    private function buscar_situacao_cadin($cnpj)
    {

        $this->load->model('situacao_cadin_model');

        $folder_pdf = FCPATH . 'arquivos/pdf-cadin-ecac-sp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $params = $this->params;
        $params['caminho_da_pasta_pdfs'] =  $folder_pdf;

        $this->load->library('Ecac/Cadin', $params, 'ecac_robo_library_eprocessos_procuracao_cadin');
        $this->ecac_robo_library_eprocessos_procuracao_cadin->set_numero_documento_procuracao($cnpj);
 
        $path_pdf = $this->ecac_robo_library_eprocessos_procuracao_cadin->baixar_pdf_cadin();
        echo $path_pdf . '<br>';

        if (empty($path_pdf))
            return;

        $pdf = new PdfToText();
        $carregado = false;
        try {
            $pdf->LoadFromString(file_get_contents($path_pdf));
            $carregado = true;
        } catch (Exception $e) {
            $carregado = false;
        }

        if ($carregado) {
            $texto_base = "NÃO INCLUÍDO PELA RFB";
            $pos = strpos($pdf->Text, $texto_base);

            $texto_base2 = "EXCLUÍDO PELA RFB";
            $pos2 = strpos($pdf->Text, $texto_base2);

            $possui_pendencia =  true;

            if ($pos !== false)
                $possui_pendencia = false;

            if ($pos2 !== false)
                $possui_pendencia = false;

            $caminho_download = $path_pdf;

            $existe_situacao = $this->situacao_cadin_model->verifica_se_existe($this->ecac_robo_library_eprocessos_procuracao_cadin->obter_numero_documento(), $this->banco);

            if ($existe_situacao->qtd > 0) {
                $this->situacao_cadin_model->update($possui_pendencia, '', $caminho_download, $this->ecac_robo_library_eprocessos_procuracao_cadin->obter_numero_documento(), $this->banco);
            } else {
                $this->situacao_cadin_model->insert($possui_pendencia, '', $caminho_download, $this->ecac_robo_library_eprocessos_procuracao_cadin->obter_numero_documento(), $this->banco);
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_eprocessos_procuracao_cadin);
        echo "<br>Busca Situação fiscal Cadin concluído";
    }


    //MENSAGENS
    private function buscar_mensagens_ecac($cnpj)
    {
        $this->load->library('Ecac/Mensagem', $this->params, 'ecac_robo_library_mensagens_ecac');
        $this->ecac_robo_library_mensagens_ecac->set_numero_documento_procuracao($cnpj);

        echo "==============CNPJ ATUAL - {$cnpj}==========\n";
        /**
         * Grava as mensagens da caixa postal
         */
        $caixa_postal = $this->ecac_robo_library_mensagens_ecac->obter_mensagem_caixa_postal();
        if ($caixa_postal)
            $this->inserir_caixa_postal($caixa_postal, $this->ecac_robo_library_mensagens_ecac->obter_numero_documento(), $this->banco);

        /**
         * Grava as parcelas do simples nacional
         */

        // $parcelas = $this->ecac_robo_library_mensagens_ecac->obter_simples_nacional_emissao_parcela();
        // if($parcelas)
        //     $this->inserir_parcelas_emitidas($parcelas, $this->ecac_robo_library_mensagens_ecac->obter_numero_documento(), $banco);
        /**
         * Grava se possui pedidos de parcelas ou não
         */

        // $possui_pedidos = $this->ecac_robo_library_mensagens_ecac->obter_simples_nacional_pedidos_parcela();
        // if($possui_pedidos)
        //     $this->inserir_consulta_pedidos($possui_pedidos, $this->ecac_robo_library_mensagens_ecac->obter_numero_documento(), $banco);
        /**
         * Emite mensagem de sucesso e resumo da operação
         */
        echo "==============SUCESSO NA OPERAÇÃO==========\n";
        // echo "Documento: {$this->ecac_robo_library_mensagens_ecac->obter_numero_documento()}\n";
        // $mensagem_pendencia = $possui_pendencia ? "Possui pendência." : "Não foram encontradas pêndencias.";
        // echo "Situação Fiscal: {$mensagem_pendencia}\n";
        // echo "PDF situação: {$path_pdf}\n";
        echo "===========================================\n";

        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_mensagens_ecac);
        echo "<br>Busca Mensagens ECAC concluído";
    }

    private function inserir_caixa_postal($data, $cnpj_data, $banco)
    {

        $this->load->model('caixa_postal_mensagem_model', 'caixa_postal_mensagem');
        $this->load->model('caixa_postal_model', 'caixa_postal');

        $result = $this->caixa_postal->existe_caixa_postal($cnpj_data, $banco);
        if ($result->qtd > 0) {
            $caixa_postal_id = $result->id;
            $this->caixa_postal->update($data, $cnpj_data, $caixa_postal_id, $banco);
        } else {
            $caixa_postal_id = $this->caixa_postal->insert($data, $cnpj_data, $banco);
        }

        $this->caixa_postal_mensagem->limpaTabelaMensagens($caixa_postal_id, $banco);

        foreach ($data['mensagens'] as $mensagem) {
            $mensagem['caixa_postal_id'] = $caixa_postal_id;
            $this->caixa_postal_mensagem->insert($mensagem, $banco);
        }
    }

    private function buscar_das_ecac_procuracao($cnpj)
    {
        $this->load->library('Ecac/SimplesNacional/Pgdas', $this->params, 'ecac_robo_library_procuracao_pgdas');
        $this->ecac_robo_library_procuracao_pgdas->set_numero_documento_procuracao($cnpj);

        $this->load->model('das_model');

        $folder_pdf = FCPATH . 'arquivos/pdf-das-ecac-sp/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $lista_das = $this->ecac_robo_library_procuracao_pgdas->obter_pgdas();
        // print_r($lista_das);
        if ($lista_das && count($lista_das) > 0)
            foreach ($lista_das as $dados) {
                $dados['cnpj'] = $cnpj;
                $existe = $this->das_model->verifica_se_existe($dados['numero_declaracao'], $this->banco, $cnpj, $dados['numero_das']);

                if ($existe->qtd > 0) {
                    $this->das_model->update($dados, $this->banco);
                } else {
                    $this->das_model->insert($dados, $this->banco);
                }
            }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_pgdas);
        echo "<br>Busca PGDAS concluído";
    }

    private function buscar_dctf_ecac_procuracao($cnpj)
    {
        $this->load->library('Ecac/Dctf', $this->params, 'ecac_robo_library_procuracao_dctf');
        $this->ecac_robo_library_procuracao_dctf->set_numero_documento_procuracao($cnpj);


        $dctf = $this->ecac_robo_library_procuracao_dctf->get_dctf($this->myhashmap);
        foreach ($dctf as $item_aux) {
            echo "CNPJ: {$cnpj} - inserido";
            $this->dctf_model->insert($item_aux, $this->banco);
        }

        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_dctf);
        echo "<br>Busca DCFT concluído";
    }

    private function buscar_vencimento_procuracao($cerficado)
    {
        /**
         * Carrega a library principal Ecac_robo_library
         */
        $params = array(
            'caminho_certificado' => 'https://veri-sp.com.br/crons-api/' . str_replace('//', '/', $cerficado->caminho_arq),
            'cerficado_senha' => $cerficado->pass,
            'caminho_da_pasta_pdfs' => ''
        );
        $this->load->library('Ecac_robo_library_eprocessos_procuracao', $params);
        $this->load->library('Ecac/SimplesNacional/Pgdas', $this->params, 'ecac_robo_library_procuracao_pgdas');

        /**
         * PROCURAÇÃO consulta
         */
        $procuracoes = $this->ecac_robo_library_eprocessos_procuracao->get_procuracoes();
        if ($procuracoes) {

            foreach ($procuracoes as $item) {
                $this->procuracao_model->insert($cerficado->cnpj_data, $item, $this->banco);
            }
        }
        unset($this->ecac_robo_library_eprocessos_procuracao);
        echo "<br>Busca vencimento procuração concluído";
    }

    private function buscar_eprocessos_procuracao($cnpj)
    {
        $this->load->model('eprocessos_ativos_historico_model');
        $this->load->model('eprocessos_ativos_model');
        $this->load->model('eprocessos_inativos_historico_model');
        $this->load->model('eprocessos_inativos_model');


        $this->load->library('Ecac/Eprocessos', $this->params, 'ecac_robo_library_procuracao_eprocessos');
        $this->ecac_robo_library_procuracao_eprocessos->set_numero_documento_procuracao($cnpj);

        $ativos = $this->ecac_robo_library_procuracao_eprocessos->get_eprocessos_ativos();
        if ($ativos) {
            foreach ($ativos as $ativo) {
                if (!isset($ativo['id']))
                    continue;
                $this->eprocessos_ativos_model->insert($cnpj, $ativo, $this->banco);

                $historico_lista = $this->ecac_robo_library_procuracao_eprocessos->get_eprocesso_historico($ativo['numero']);
                foreach ($historico_lista as $historico) {
                    $this->eprocessos_ativos_historico_model->insert($historico, $ativo['id'], $this->banco);
                }
            }
        }

        $inativos = $this->ecac_robo_library_procuracao_eprocessos->get_eprocessos_inativos();
        print_r($inativos);

        if ($inativos) {

            foreach ($inativos as $inativo) {
                if (!isset($inativo['id']))
                    continue;
                $this->eprocessos_inativos_model->insert($cnpj, $inativo, $this->banco);

                $historico_lista = $this->ecac_robo_library_procuracao_eprocessos->get_eprocesso_historico($inativo['numero']);
                foreach ($historico_lista as $historico) {
                    $this->eprocessos_inativos_historico_model->insert($historico, $inativo['id'], $this->banco);
                }
            }
        }

        unset($this->ecac_robo_library_eprocessos_procuracao);
        echo "<br>Busca eprocessos procuração concluído";
    }

    private function buscar_simplesnacional_pedidos_parcelamento($cnpj)
    {
        $this->load->model('Simplesnacional_debitos_parcelas_model');
        $this->load->model('Simplesnacional_demonstrativo_pagamentos_model');
        $this->load->model('Simplesnacional_pedidos_parcelamentos_model');

        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-simplesnacional/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->load->library('Ecac/SimplesNacional/Parcelamento', $this->params, 'ecac_robo_library_procuracao_simplesnacional');


        $registros = $this->ecac_robo_library_procuracao_simplesnacional->obter_parcelamento();
        // echo '<pre>';
        // print_r($registros); echo'</pre>'; die();
        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_pedido = $this->Simplesnacional_pedidos_parcelamentos_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

            if ($existe_pedido->qtd > 0) {
                $id_parcelamento = $existe_pedido->id;
                $this->Simplesnacional_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Em parcelamento')
                    continue;
                $id_parcelamento = $this->Simplesnacional_pedidos_parcelamentos_model->insert($registro, $this->banco);
            }

            $existe_debitos_parcelas = $this->Simplesnacional_debitos_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

            if ($existe_debitos_parcelas->qtd <= 0) {
                foreach ($registro['relacao_debitos_parcelas'] as $rdp) {
                    // $this->Simplesnacional_debitos_parcelas_model->clear($registro['cnpj'], $banco);
                    $rdp['cnpj'] = $cnpj;
                    $rdp['id_parcelamento'] = $id_parcelamento;
                    $this->Simplesnacional_debitos_parcelas_model->insert($rdp, $this->banco);
                }
            }


            foreach ($registro['demonstrativo_pagamentos'] as $dp) {
                $dp['cnpj'] = $cnpj;
                $dp['id_parcelamento'] = $id_parcelamento;

                $existe_pagamento = $this->Simplesnacional_demonstrativo_pagamentos_model->verifica_se_existe($dp['cnpj'], $this->banco, $id_parcelamento, $dp['mes_parcela']);
                if ($existe_pagamento->qtd <= 0) {
                    $this->Simplesnacional_demonstrativo_pagamentos_model->insert($dp, $this->banco);
                }
                // $this->Simplesnacional_demonstrativo_pagamentos_model->clear($registro['cnpj'], $banco);
            }
        }
        //Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_simplesnacional);
        echo "<br>Busca simplesnacional pedidos parcelamento concluído";
    }


    private function buscar_simplesnacional_parcelas($cnpj)
    {
        $this->load->model('Simplesnacional_emissao_parcela_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento', $this->params, 'ecac_robo_library_procuracao_simplesnacional_parcela');

        $parcelas = $this->ecac_robo_library_procuracao_simplesnacional_parcela->obter_simples_nacional_emissao_parcela();

        if ($parcelas) {
            foreach ($parcelas as $parcela) {

                $result = $this->Simplesnacional_emissao_parcela_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Simplesnacional_emissao_parcela_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Simplesnacional_emissao_parcela_model->insert($cnpj, $this->banco, $parcela);
                }

                if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                    try {
                        $this->baixar_pdf_simplesnacional($this->banco, $cnpj, trim($parcela['data_parcela']));
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_simplesnacional_parcela);
        echo "<br>Busca parcela simplesnacional concluído";
    }

    private function baixar_pdf_simplesnacional($banco, $cnpj, $data_parcela)
    {
        $caminho_download = $this->ecac_robo_library_procuracao_simplesnacional_parcela->gerar_parcela_simplesnacional($data_parcela);
        if ($caminho_download != "" && $caminho_download != 'ERROECAC') {
            echo "<br> $caminho_download";
            $this->Simplesnacional_emissao_parcela_model->update_path($banco, $data_parcela, $cnpj, $caminho_download);
            return $caminho_download;
        }else echo "<br> $caminho_download";

    }

    private function buscar_pert_pedidos_parcelamento($cnpj)
    {
        $this->load->model('Pert_debitos_parcelas_model');
        $this->load->model('Pert_demonstrativo_pagamentos_model');
        $this->load->model('Pert_pedidos_parcelamentos_model');

        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-pert/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }
        $this->params['caminho_da_pasta_pdfs']=$folder_pdf;
        $this->load->library('Ecac/SimplesNacional/Parcelamento_pert', $this->params, 'ecac_robo_library_procuracao_pert');

        $registros = $this->ecac_robo_library_procuracao_pert->obter_parcelamento();
 
        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_pedido = $this->Pert_pedidos_parcelamentos_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

            if ($existe_pedido->qtd > 0) {
                $id_parcelamento = $existe_pedido->id;
                $this->Pert_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Em parcelamento')
                    continue;
                $id_parcelamento = $this->Pert_pedidos_parcelamentos_model->insert($registro, $this->banco);
            }


            $existe_debitos_parcelas = $this->Pert_debitos_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

            if ($existe_debitos_parcelas->qtd <= 0) {
                foreach ($registro['relacao_debitos_parcelas'] as $rdp) {
                    // $this->Pert_debitos_parcelas_model->clear($registro['cnpj'], $this->banco);
                    $rdp['cnpj'] = $cnpj;
                    $rdp['id_parcelamento'] = $id_parcelamento;
                    $this->Pert_debitos_parcelas_model->insert($rdp, $this->banco);
                }
            }


            foreach ($registro['demonstrativo_pagamentos'] as $dp) {
                $dp['cnpj'] = $cnpj;
                $dp['id_parcelamento'] = $id_parcelamento;

                $existe_pagamento = $this->Pert_demonstrativo_pagamentos_model->verifica_se_existe($dp['cnpj'], $this->banco, $id_parcelamento, $dp['mes_parcela']);
                if ($existe_pagamento->qtd <= 0) {
                    $this->Pert_demonstrativo_pagamentos_model->insert($dp, $this->banco);
                }
                // $this->Pert_demonstrativo_pagamentos_model->clear($registro['cnpj'], $banco);
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_pert);
        echo "<br>Busca pert pedidos parcelamento concluído";
    }

    private function buscar_pert_parcelas($cnpj)
    {
        $this->load->model('Parcelas_pert_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_pert', $this->params, 'ecac_robo_library_procuracao_pert_parcelas');

        $parcelas = $this->ecac_robo_library_procuracao_pert_parcelas->obter_parcelas_pert();

        if ($parcelas) {
            foreach ($parcelas as $parcela) {

                $result = $this->Parcelas_pert_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Parcelas_pert_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Parcelas_pert_model->insert($cnpj, $this->banco, $parcela);
                }
                if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                    try {
                        $this->baixar_pdf_pert($this->banco, $cnpj, trim($parcela['data_parcela']));
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_pert_parcelas);
        echo "<br>Busca pert parcelas concluído";
    }

    private function baixar_pdf_pert($banco, $cnpj, $data_parcela)
    {
        $caminho_download = $this->ecac_robo_library_procuracao_pert_parcelas->gerar_parcela_pert($data_parcela);
        echo "<br>$caminho_download";
        if ($caminho_download != "") {
            $this->Parcelas_pert_model->update_path($banco, $data_parcela, $cnpj, $caminho_download);
            return $caminho_download;
        }
    }

    private function buscar_parcelamento_nao_previdenciario($cnpj)
    {
        $this->load->library('Ecac/SimplesNacional/Parcelamento_nao_previdenciario', $this->params, 'ecac_robo_library_procuracao_nao_previdenciario');

        $this->load->model('Parcelamento_nao_previdenciario_processos_negociados_model');
        $this->load->model('Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model');
        $this->load->model('Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model');

        $folder_pdf = FCPATH . 'arquivos/parcelamento-nao-prividenciario/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $registros = $this->ecac_robo_library_procuracao_nao_previdenciario->processos_negociados();

        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_registro = $this->Parcelamento_nao_previdenciario_processos_negociados_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['processo']);

            if ($existe_registro->qtd > 0) {
                $id_processo = $existe_registro->id;
                if ($registro['situacao'] != 'Parcelado')
                    $this->Parcelamento_nao_previdenciario_processos_negociados_model->update($registro['cnpj'], $this->banco, $registro['processo'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Parcelado')
                    continue;
                $id_processo = $this->Parcelamento_nao_previdenciario_processos_negociados_model->insert($registro, $this->banco);
            }

            foreach ($registro['tributos_do_processo_negociados'] as $tributos_do_processo) {
                $existe_tributos_processos_negociados = $this->Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_processo, $tributos_do_processo['tributo']);
                $tributos_do_processo['id_processo'] = $id_processo;
                $tributos_do_processo['cnpj'] = $cnpj;

                if ($existe_tributos_processos_negociados->qtd > 0) {
                    $id_tributo = $this->Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model->update($tributos_do_processo, $this->banco);
                } else {
                    $id_tributo = $this->Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model->insert($tributos_do_processo, $this->banco);
                }

                foreach ($tributos_do_processo['demonstrativo_das_parcelas'] as $demonstrativo) {
                    $existe =  $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_tributo, $demonstrativo['numero_parcela']);
                    $demonstrativo['id_tributo'] = $id_tributo;
                    $demonstrativo['cnpj'] = $cnpj;
                    if ($existe->qtd > 0) {
                        $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->update($demonstrativo, $this->banco);
                    } else {
                        $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->insert($demonstrativo, $this->banco);
                    }
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_nao_previdenciario);
        echo "<br>Busca Parcelamento Não Previdenciário concluído";
    }
}
