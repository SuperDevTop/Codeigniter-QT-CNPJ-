<?php

function obter_certificado_dec($banco, $cnpj){
    $ci =& get_instance();

    $certificado_procuracao = $ci->certificadocontador_model->find_certificado_procuracao_dec($cnpj, $banco);
    if (!empty($certificado_procuracao)){
        return array(
          'certificado' => $certificado_procuracao,
          'tipo' => 'procuracao'
        );
    }

    $certificado_individual = $ci->certificado_model->find_certificado($cnpj, $banco);
    if (!empty($certificado_individual)){
        return array(
            'certificado' => $certificado_individual,
            'tipo' => 'individual'
        );
    }
}

function obter_certificado_ecac($banco, $cnpj){
    $ci =& get_instance();

    $certificado_procuracao = $ci->certificadocontador_model->find_certificado_procuracao_ecac($cnpj, $banco);
    if (!empty($certificado_procuracao)){
        return array(
            'certificado' => $certificado_procuracao,
            'tipo' => 'procuracao'
        );
    }

    $certificado_individual = $ci->certificado_model->find_certificado($cnpj, $banco);
    if (!empty($certificado_individual)){
        return array(
            'certificado' => $certificado_individual,
            'tipo' => 'individual'
        );
    }
}