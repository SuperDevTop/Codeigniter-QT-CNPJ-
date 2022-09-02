<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron_certificado_individual extends CI_Controller
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


    public function buscar_todos(){

        $bancos = ['17819704000193','40600261000167','30571287000170','35295767000170','08189174000156','32075671000108','positivocontabilidade','16800170000190','10224502000150','11918627000142','10730586000101','13914050000126','08003843000153','washingtonluiz','27270392000165','18301464000101','00760815000179','08272991764','06005889000186','09040832000106','28554841000160','40497040000105','13914050000126','15280789000159','19363928000169','29955404000110','10220188000137','39745613000193','07247520000142','27458816000209','41246249000169','paulocesar','24238189000113','15055691000106','36168709000148','11434725000104','09173665000171','39500292000167','12508292000157','21391648000106','34349420000108','29322338000141','80128084120','73673056549','34546437000147','00059279000189','11899191000191','43469297000141','11899191000191','34546437000147','03626522000191','18451386000113','77595411000100','17535716000196','34317229000176','37930976000173','94643285000122','33429789000150','03626522000191','35917551000107','28170127000178','33429789000150','27897267000180'];

        foreach ($bancos as $nomeBanco) { 

            $this->buscar($nomeBanco);
            $this->cron_situacao_fiscal($nomeBanco);
        }
    }

    function buscar($banco_de_dados)
    {
        include('PdfToText/PdfToText.phpclass');

        $this->banco = $banco_de_dados;

        $this->config->load('ecac_robo_config');

        $this->load->model('certificado_model', 'certificado');
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

         
            $this->load->library('Ecac/Ecac_certificado', $this->params, 'ecac_robo_library_procuracao');
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

            // echo "<br>Buscando dados da empresa: $item->cnpj <br>";
            $this->ecac_robo_library_procuracao->set_numero_documento_procuracao($cerficado->cnpj_data);
            $this->ecac_robo_library_procuracao->setar_numero_documento_certificado($cerficado->cnpj_data);

            $this->buscar_mensagens_ecac($cerficado->cnpj_data);
            $this->buscar_das_ecac_procuracao($cerficado->cnpj_data);
            $this->buscar_eprocessos_procuracao($cerficado->cnpj_data);
            $this->buscar_dctf_ecac_procuracao($cerficado->cnpj_data);
            // $this->buscar_situacao_fiscal_ecac($cerficado->cnpj_data);
            $this->buscar_situacao_cadin($cerficado->cnpj_data);
            
            $this->buscar_simplesnacional_pedidos_parcelamento($cerficado->cnpj_data);
            $this->buscar_simplesnacional_parcelas($cerficado->cnpj_data);
            $this->buscar_pert_pedidos_parcelamento($cerficado->cnpj_data);
            $this->buscar_pert_parcelas($cerficado->cnpj_data);
            $this->buscar_parcelamento_nao_previdenciario($cerficado->cnpj_data);
            $this->buscar_parcelamento_lei_12996($cerficado->cnpj_data);
            $this->buscar_parcelamento_pert_rfb($cerficado->cnpj_data);
            $this->buscar_parcelamento_mei($cerficado->cnpj_data);
            $this->buscar_parcelas_mei($cerficado->cnpj_data);
            // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
            unset($this->ecac_robo_library_procuracao);
        }
    }

    function cron_situacao_fiscal($banco_de_dados){

        $banco = $banco_de_dados;

        $this->config->load('ecac_robo_config');
        $this->load->model('certificado_cron_model', 'certificado');
        date_default_timezone_set('America/Bahia');

        $cerficados = $this->certificado->get($banco);
        
        $folder_pdf = FCPATH . 'arquivos/pdf-certidao-ecac-sp/'.$banco.'/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '1200000');
        foreach ($cerficados as $cerficado){

            /**
             * Carrega a library principal Ecac_robo_library
             */
            $params = array('caminho_certificado' => 'https://veri-sp.com.br/crons-api/'.str_replace('//','/', $cerficado->caminho_arq ) ,
            'cerficado_senha' => $cerficado->pass,
            'caminho_da_pasta_pdfs' => $folder_pdf);
            $this->load->library('Ecac_robo_eprocessos_library', $params);

            /**
             * Verifica se o acesso foi validado com sucesso, caso contrário pula para o próximo
             */
            if(!$this->ecac_robo_eprocessos_library->acesso_valido()){
                unset($this->ecac_robo_eprocessos_library);
                continue;
            }

            /**
             * Grava a situação fiscal e o pdf
             */

            $path_pdf = $this->ecac_robo_eprocessos_library->baixar_pdf_situacao_fiscal();

            $caminho_server = $path_pdf[0];
            $caminho_google = $path_pdf[1];
                
            $carregado = false;
            $pdf    =  new PdfToText() ;

            try{
                $pdf->Load( $caminho_server ) ;
                $carregado = true;
            }catch (Exception $e){
                continue;
            } 
            
            $texto_base = "Pendência -";
            $pos = strpos($pdf->Text, $texto_base);

            $possui_pendencia =  false;

            if ($pos !== false)
                $possui_pendencia = true;

            // $caminho_aux = str_replace("/var/www/html", "",$path_pdf);

            $caminho_download = "https://storage.googleapis.com/cron-veri-files-br/".$caminho_google;

            $existe_situacao = $this->verifica_se_existe_situacao($this->ecac_robo_eprocessos_library->obter_numero_documento(), $banco);
            if($existe_situacao > 0){
                $this->update_situacao_fiscal($possui_pendencia, $caminho_server, $caminho_download, $this->ecac_robo_eprocessos_library->obter_numero_documento(), $banco);
            }else{
                $this->inserir_situacao_fiscal($possui_pendencia, $caminho_server, $caminho_download, $this->ecac_robo_eprocessos_library->obter_numero_documento(), $banco);
            }

            echo "==============SUCESSO NA OPERAÇÃO==========\n";
            echo "Documento: {$this->ecac_robo_eprocessos_library->obter_numero_documento()}\n";
            $mensagem_pendencia = $possui_pendencia ? "Possui pendência." : "Não foram encontradas pêndencias.";
            echo "Situação Fiscal: {$mensagem_pendencia}\n";
            echo "PDF situação: {$caminho_server}\n";
            echo "===========================================\n";


            unset($this->ecac_robo_eprocessos_library);

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

       $this->load->library('Ecac/SituacaoFiscal/SituacaoFiscalCertificado', $params, 'ecac_robo_situacao_fiscal_ecac');
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
        if ($lista_das && count($lista_das) > 0){
            foreach ($lista_das as $dados) {
                $dados['cnpj'] = $cnpj;
                $existe = $this->das_model->verifica_se_existe($dados['numero_declaracao'], $this->banco, $cnpj, $dados['numero_das']);

                if ($existe->qtd > 0) {
                    $this->das_model->update($dados, $this->banco);
                } else {
                    $this->das_model->insert($dados, $this->banco);
                }

                //gerar recibo
                // if ((!isset($existe->caminho_download_recibo) || empty($existe->caminho_download_recibo))) {
                //     try {
                //         $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_recibo($folder_pdf, $dados['numero_declaracao'], date('Y'));
                //         echo "<br>({$dados['compentencia']})recibo: $caminho_download";
                //         if ($caminho_download != "") {
                //             $this->das_model->update_caminho_download_recibo($caminho_download, $dados['numero_declaracao'], $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
                //gerar declaração
                if ((!isset($existe->caminho_download_declaracao) || empty($existe->caminho_download_declaracao))) {
                    try {
                        $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_declaracao($folder_pdf, $dados['numero_declaracao'], date('Y'));
                        echo "<br>({$dados['compentencia']})declaracao: $caminho_download";
                        if ($caminho_download != "") {
                            $this->das_model->update_caminho_download_declaracao($caminho_download, $dados['numero_declaracao'], $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                // //extrato
                // if ((!isset($existe->caminho_download_extrato) || empty($existe->caminho_download_extrato))) {
                //     try {
                //         $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_extrato($folder_pdf, $dados['numero_declaracao'], $dados['numero_das'], date('Y'));
                //         echo "<br>({$dados['compentencia']})extrato: $caminho_download<br>";
                //         if ($caminho_download != "") {
                //             $this->das_model->update_caminho_download_extrato($caminho_download, $dados['numero_declaracao'], $this->banco);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }

            }

            // gerar das
            // foreach ($lista_das as $dados) {
            //     $das_pago = $this->das_model->verifica_se_pago($this->banco, $cnpj, $dados['compentencia']);
            //     //se o das for == 0 é pq não foi pago
            //     if ($das_pago->qtd == 0) {
            //         $existe = $this->das_model->verifica_se_existe_caminho_download_das($this->banco, $cnpj, $dados['compentencia']);
            //         echo '<br>'.substr($dados['compentencia'], 3);
            //         try {
            //             if ((!isset($existe->caminho_download_das) || empty($existe->caminho_download_das))) {
            //                 $caminho_download = $existe->caminho_download_das;
            //             } else {
            //                 $tentativa = 5;
            //                 while ($tentativa) {
            //                     $tentativa--;

            //                     $caminho_download = $this->ecac_robo_library_procuracao_pgdas->obter_das($folder_pdf, substr($dados['compentencia'], 3));

            //                     if ($caminho_download != "")
            //                         $tentativa = 0;
            //                 }
            //             }

            //             echo "<br>gerar das: $caminho_download";
            //             if ($caminho_download != "") {
            //                 $this->das_model->update_caminho_download_das($caminho_download, $dados['numero_declaracao'], $this->banco);
            //             }
            //         } catch (Exception $e) {
            //             echo $e->getMessage();
            //         }
            //     }
            // }
        
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
            'caminho_certificado' => 'https://veri-ba.com.br/crons-api/' . str_replace('//', '/', $cerficado->caminho_arq),
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

            if($registro['situacao'] == "Em parcelamento"){
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
            }else{
                $registro['cnpj'] = $cnpj;
                $this->Simplesnacional_pedidos_parcelamentos_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
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

                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $this->baixar_pdf_simplesnacional($this->banco, $cnpj, trim($parcela['data_parcela']));
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
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
                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $this->baixar_pdf_pert($this->banco, $cnpj, trim($parcela['data_parcela']));
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
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
        $folder_pdf = FCPATH . 'arquivos/parcelamento-nao-prividenciario/' . $this->banco . '/';

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->library('Ecac/SimplesNacional/Parcelamento_nao_previdenciario', $this->params, 'ecac_robo_library_procuracao_nao_previdenciario');

        $this->load->model('Parcelamento_nao_previdenciario_processos_negociados_model');
        $this->load->model('Parcelamento_nao_previdenciario_tributos_do_processo_negociados_model');
        $this->load->model('Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model');

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
                    $result =  $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_tributo, $demonstrativo['numero_parcela']);
                    $demonstrativo['id_tributo'] = $id_tributo;
                    $demonstrativo['cnpj'] = $cnpj;

                    if ($result->qtd > 0) {
                        $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->update($demonstrativo, $this->banco);
                    } else {
                        $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->insert($demonstrativo, $this->banco);
                    }

                    // if ($demonstrativo->situacao != 'Paga'){
                    //     if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                    //         try {
                    //             $caminho_download = $this->ecac_robo_library_procuracao_nao_previdenciario->gerar_parcela_nao_previdenciario($registro['processo'], $tributos_do_processo['tributo'], $demonstrativo['data_vencimento'], $demonstrativo['valor_ate_vencimento'], $demonstrativo['numero_parcela']);
                    //             echo "<br>Caminho: $caminho_download";
                    //             if ($caminho_download != "") {
                    //                 $this->Parcelamento_nao_previdenciario_demonstrativo_das_parcelas_model->update_path($this->banco, $demonstrativo['numero_parcela'], $demonstrativo['cnpj'], $demonstrativo['id_tributo'], $caminho_download);
                    //             }
                    //         } catch (Exception $e) {
                    //             echo $e->getMessage();
                    //         }
                    //     }
                    // }
                    
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_nao_previdenciario);
        echo "<br>Busca Parcelamento Não Previdenciário concluído";
    }

    private function buscar_parcelamento_lei_12996($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/parcelamento-lei-12996/' . $this->banco . '/';
        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Parcelamento_lei_12996_divida_consolidada_model');
        $this->load->model('Parcelamento_lei_12996_demonstrativo_prestacoes_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_lei_12996', $this->params, 'ecac_robo_library_procuracao_lei_12996');

        $mes_atual = date('Ym');

        $registros = $this->ecac_robo_library_procuracao_lei_12996->extrato_e_demonstrativos($cnpj);
        print_r($registros);
        if (!empty($registros)) {
            foreach ($registros as $registro) {
                $registro['cnpj'] = $cnpj;

                // $existe_registro = $this->Parcelamento_lei_12996_divida_consolidada_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['cod_modalidade']);
                $existe_registro = $this->Parcelamento_lei_12996_divida_consolidada_model->verifica_se_existe($registro, $this->banco);

                if ($existe_registro->qtd > 0) {
                    $id_divida_consolidada = $existe_registro->id;
                    $this->Parcelamento_lei_12996_divida_consolidada_model->update($registro, $id_divida_consolidada, $this->banco);
                    // $this->Parcelamento_lei_12996_divida_consolidada_model->update($registro['cnpj'], $this->banco, $registro['data_adesao'],  $registro['nome_situacao']);
                } else {
                    $id_divida_consolidada = $this->Parcelamento_lei_12996_divida_consolidada_model->insert($registro, $this->banco);
                }

                foreach ($registro['parcelas'] as $parcela) {
                    if ($parcela['data_parcela'] != '00000000') {
                    $parcela['cnpj'] = $cnpj;
                    $parcela['id_divida_consolidada'] = $id_divida_consolidada;

                    $result = $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->verifica_se_existe($cnpj, $this->banco, $parcela['parcela_id'], $id_divida_consolidada);
                    if ($result->qtd > 0) {
                        $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->update($parcela, $this->banco);
                    } else {
                        $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->insert($parcela, $this->banco);
                    }

                    // if (substr($parcela['data_parcela'], 0, -2) == $mes_atual && (!isset($result->path_download_parcela) || empty($result->path_download_parcela))) {
                    //     try {
                    //         $caminho_download = $this->ecac_robo_library_procuracao_lei_12996->gerar_parcela_lei_12996($cnpj, $parcela['data_parcela'], $registro['cod_receita']);
                    //         echo "<br>$caminho_download";
                    //         if ($caminho_download != "") {
                    //             $this->Parcelamento_lei_12996_demonstrativo_prestacoes_model->update_path($caminho_download, $parcela, $this->banco);
                    //         }
                    //     } catch (Exception $e) {
                    //         echo $e->getMessage();
                    //     }
                    // }
                    }
                }
            }
        }
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_library_procuracao_lei_12996);
        echo "<br>Busca Parcelamento Lei 12.996 concluído";
    }

    private function buscar_parcelamento_pert_rfb($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/parcelamento-pert-rfb/' . $this->banco . '/';

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Parcelamento_pert_rfb_model');
        $this->load->model('Parcelamento_pert_rfb_demonstrativo_de_parcelas_model');
        $this->load->model('Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_pert_rfb', $this->params, 'ecac_robo_library_procuracao_pert_rfb');

        $mes_atual = date('m/Y');

        $parcelamentos = $this->ecac_robo_library_procuracao_pert_rfb->obter_parcelamentos($cnpj);

        if (!is_null($parcelamentos)) {
            foreach ($parcelamentos as $parcelamento) {
                if ($parcelamento->situacao == 'Ativo') {
                    $parcelamento->cnpj = $cnpj;

                    $existe_registro = $this->Parcelamento_pert_rfb_model->verifica_se_existe($parcelamento, $this->banco);

                    if ($existe_registro->qtd == 0) {
                        $id_parcelamento = $this->Parcelamento_pert_rfb_model->insert($parcelamento, $this->banco);
                    } else {
                        $id_parcelamento = $existe_registro->id;
                        $this->Parcelamento_pert_rfb_model->update($parcelamento, $this->banco);
                    }

                    foreach ($parcelamento->demonstrativo_de_pagamentos->pagamentosDtos as $pagamento) {
                        $pagamento->cnpj = $cnpj;
                        $pagamento->id_parcelamento = $id_parcelamento;
                        $pagamento->parcelamentoFoiReconsolidado = $parcelamento->demonstrativo_de_pagamentos->parcelamentoFoiReconsolidado;
                        $pagamento->simboloMoeda = $parcelamento->demonstrativo_de_pagamentos->simboloMoeda;

                        $existe_pagamento = $this->Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model->verifica_se_existe($pagamento, $this->banco);

                        if ($existe_pagamento->qtd == 0) {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model->insert($pagamento, $this->banco);
                        } else {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_pagamentos_model->update($pagamento, $this->banco);
                        }
                    }

                    foreach ($parcelamento->demonstrativo_de_parcelas[0]->parcelas as $parcela) {
                        $parcela->cnpj = $cnpj;
                        $parcela->id_parcelamento = $id_parcelamento;

                        $existe_parcela = $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->verifica_se_existe($parcela, $this->banco);

                        if ($existe_parcela->qtd == 0) {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->insert($parcela, $this->banco);
                        } else {
                            $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->update($parcela, $this->banco);
                        }

                        if (substr($parcela->dataVencimento, 3) == $mes_atual && (!isset($existe_parcela->path_download_parcela) || empty($existe_parcela->path_download_parcela))) {
                            try {
                                $caminho_download = $this->ecac_robo_library_procuracao_pert_rfb->gerar_parcela_pert_rfb($parcelamento->idFormatado, $parcela->id);
                                echo "<br>$caminho_download";
                                if ($caminho_download != "") {
                                    $this->Parcelamento_pert_rfb_demonstrativo_de_parcelas_model->update_path($parcela, $this->banco, $caminho_download);
                                }
                            } catch (Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                    }
                }
            }

            unset($this->ecac_robo_library_procuracao_pert_rfb);
            echo "<br>Busca Parcelamento Pert RFB concluído";
        } else echo "<br>Não possui Parcelamento Pert RFB";
    }

    private function buscar_parcelamento_mei($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/' . $this->banco . '/';
        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->model('Parcelamento_mei_consolidacao_original_model');
        $this->load->model('Parcelamento_mei_demonstrativo_pagamento_model');
        $this->load->model('Parcelamento_mei_pedido_contribuinte_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_mei', $this->params, 'ecac_robo_library_procuracao_mei');


        $registros = $this->ecac_robo_library_procuracao_mei->obter_parcelamentos();

        foreach ($registros as $registro) {
            $registro['cnpj'] = $cnpj;

            $existe_pedido = $this->Parcelamento_mei_pedido_contribuinte_model->verifica_se_existe($registro['cnpj'], $this->banco, $registro['numero']);

            if ($existe_pedido->qtd > 0) {
                $id_parcelamento = $existe_pedido->id;
                $this->Parcelamento_mei_pedido_contribuinte_model->update($registro['cnpj'], $this->banco, $registro['numero'],  $registro['situacao']);
            } else {
                if ($registro['situacao'] != 'Em parcelamento')
                    continue;
                $id_parcelamento = $this->Parcelamento_mei_pedido_contribuinte_model->insert($registro, $this->banco);
            }

            $existe_debitos_parcelas = $this->Parcelamento_mei_consolidacao_original_model->verifica_se_existe($registro['cnpj'], $this->banco, $id_parcelamento);

            if ($existe_debitos_parcelas->qtd <= 0) {
                foreach ($registro['consolidacao_original'] as $consolidacao) {
                    $consolidacao['cnpj'] = $cnpj;
                    $consolidacao['id_parcelamento'] = $id_parcelamento;
                    $this->Parcelamento_mei_consolidacao_original_model->insert($consolidacao, $this->banco);
                }
            }


            foreach ($registro['demonstrativo_pagamento'] as $demonstrativo) {
                $demonstrativo['cnpj'] = $cnpj;
                $demonstrativo['id_parcelamento'] = $id_parcelamento;

                $existe_pagamento = $this->Parcelamento_mei_demonstrativo_pagamento_model->verifica_se_existe($demonstrativo['cnpj'], $this->banco, $id_parcelamento, $demonstrativo['mes_parcela']);
                if ($existe_pagamento->qtd <= 0) {
                    $this->Parcelamento_mei_demonstrativo_pagamento_model->insert($demonstrativo, $this->banco);
                }
            }
        }
        unset($this->ecac_robo_situacao_fiscal_ecac_mei);
        echo "<br>Busca Parcelamento MEI concluído";
    }

    private function buscar_parcelas_mei($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/recibos-parcelamento-mei/' . $this->banco . '/';
        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;
        
        $this->load->model('Parcelas_mei_model');

        $this->load->library('Ecac/SimplesNacional/Parcelamento_mei', $this->params, 'ecac_robo_library_procuracao_mei');

        $parcelas = $this->ecac_robo_library_procuracao_mei->obter_parcelas_mei();
        if ($parcelas) {
            $mes_atual = date('m/Y');
            foreach ($parcelas as $parcela) {
                //0 para parcela não paga e 1 para parcela paga e -1 para parcela atrasada
                $parcela['pago'] = $this->Parcelas_mei_model->verifica_se_pago($this->banco, $cnpj, $parcela['data_parcela'])->qtd;
                if ($parcela['pago'] == 0 && $parcela['data_parcela'] < $mes_atual) {
                    $parcela['pago'] = -1;
                }

                $result = $this->Parcelas_mei_model->verifica_se_existe($this->banco, $cnpj, $parcela['data_parcela']);
                if ($result->qtd > 0) {
                    $this->Parcelas_mei_model->update($cnpj, $this->banco, $parcela);
                } else {
                    $this->Parcelas_mei_model->insert($cnpj, $this->banco, $parcela);
                }

                //Gerar parcela
                // if (!isset($result->path_download_parcela) || empty($result->path_download_parcela)) {
                //     try {
                //         $caminho_download = $this->ecac_robo_library_procuracao_mei->gerar_parcela_mei($parcela['data_parcela']);
                //         echo "<br>$caminho_download";
                //         if ($caminho_download != "") {
                //             $this->Parcelas_mei_model->update_path($this->banco, $parcela['data_parcela'], $cnpj, $caminho_download);
                //         }
                //     } catch (Exception $e) {
                //         echo $e->getMessage();
                //     }
                // }
            }
        }
        unset($this->ecac_robo_situacao_fiscal_ecac_mei);
        echo "<br>Busca Parcelamento MEI concluído";
    }

    private function buscar_dctf_web_teste($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/dctf-web/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->library('Ecac/Dctf_web', $this->params, 'ecac_robo_dctf_web');

        $this->load->model('Dctf_web_model');
        $this->load->model('Dctf_web_detalhes_model');

        $registros = $this->ecac_robo_dctf_web->buscar_declaracoes();
        
        if($registros){
           foreach ($registros as $registro) {
                $registro['cnpj'] = $cnpj;
                
                $existe_registro = $this->Dctf_web_model->verifica_se_existe($registro, $this->banco);
                
                if ($existe_registro->qtd > 0) {
                    $id = $existe_registro->id;
                    $this->Dctf_web_model->update($registro, $id, $this->banco);
                } else {
                    $id = $this->Dctf_web_model->insert($registro, $this->banco);
                }

                $this->Dctf_web_detalhes_model->clear($id, $this->banco);
                foreach ($registro['detalhes'] as $detalhe) {
                    $this->Dctf_web_detalhes_model->insert($detalhe, $id, $this->banco);
                }

                if (!isset($existe_registro->path_download_darf) || empty($existe_registro->path_download_darf)) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_darf($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_darf($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }

                if (!isset($existe_registro->path_download_recibo) || empty($existe_registro->path_download_recibo)) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_recibo($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_recibo($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }

                if (!isset($existe_registro->path_download_extrato) || empty($existe_registro->path_download_extrato)) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_extrato($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_extrato($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            } 
        }
        
        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_dctf_web);
        echo "<br>Busca DCTF Web concluído";
    }

    private function buscar_dctf_web($cnpj)
    {
        $folder_pdf = FCPATH . 'arquivos/dctf-web/' . $this->banco . '/';

        if (!file_exists($folder_pdf)) {
            mkdir($folder_pdf, DIR_WRITE_MODE, true);
        }

        $this->params['caminho_da_pasta_pdfs'] = $folder_pdf;

        $this->load->library('Ecac/Dctf_web', $this->params, 'ecac_robo_dctf_web');

        $this->load->model('Dctf_web_model');
        $this->load->model('Dctf_web_detalhes_model');

        $registros = $this->ecac_robo_dctf_web->buscar_declaracoes();

        if ($registros) {
            foreach ($registros as $registro) {
                $registro['cnpj'] = $cnpj;

                $existe_registro = $this->Dctf_web_model->verifica_se_existe($registro, $this->banco);

                if ($existe_registro->qtd > 0) {
                    $id = $existe_registro->id;
                    $this->Dctf_web_model->update($registro, $id, $this->banco);
                } else {
                    $id = $this->Dctf_web_model->insert($registro, $this->banco);
                }

                if (!empty($registro['detalhes'])) {
                    $this->Dctf_web_detalhes_model->clear($id, $this->banco);
                    foreach ($registro['detalhes'] as $detalhe) {
                        $this->Dctf_web_detalhes_model->insert($detalhe, $id, $this->banco);
                    }
                }

                if (($registro['status'] == '0' && $registro['situacao'] == 'Ativa') && (!isset($existe_registro->path_download_darf) || empty($existe_registro->path_download_darf))) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_darf($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_darf($id, $caminho_download, $this->banco);
                        }else 'caminho vazio';
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }

                if ($registro['situacao'] == 'Ativa' && (!isset($existe_registro->path_download_recibo) || empty($existe_registro->path_download_recibo))) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_recibo($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_recibo($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }

                if ($registro['situacao'] == 'Ativa' && (!isset($existe_registro->path_download_extrato) || empty($existe_registro->path_download_extrato))) {
                    try {
                        $caminho_download = $this->ecac_robo_dctf_web->gerar_extrato($registro['id_declaracao'], $registro['id_controle']);
                        echo "<br>$caminho_download";
                        if ($caminho_download != "") {
                            $this->Dctf_web_model->update_path_extrato($id, $caminho_download, $this->banco);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }

        // Tem que fazer unset pra ele executar  o destrutor da library e encerrar a connection
        unset($this->ecac_robo_dctf_web);
        echo "<br>Busca DCTF Web concluído";
    }
}