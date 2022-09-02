<?php

class Eprocessos_ativos_historico_model extends CI_Model {

    public  $idProcesso;
    public  $dataEntrada;
    public  $dataEntradaFormatada;
    public  $equipeOuOperacao;
    public  $nomeAtividade;
    public  $siglaUnidade;
    public  $tempoAtividade;
    public  $tempoMedioAtividade;

    public function clear($banco){
        return $this->db->truncate($banco.'.dtb_ecac_eprocessos_ativos_historico');
    }

    public function insert($historico, $idProcesso, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->idProcesso = $idProcesso;
        $this->dataEntrada = $historico['dataEntrada'];
        $this->dataEntradaFormatada = $historico['dataEntradaFormatada'];
        $this->equipeOuOperacao = $historico['equipeOuOperacao'];
        $this->nomeAtividade = $historico['nomeAtividade'];
        $this->siglaUnidade = $historico['siglaUnidade'];
        $this->tempoAtividade = $historico['tempoAtividade'];
        $this->tempoMedioAtividade = $historico['tempoMedioAtividade'];

        $this->db->insert($banco.'.dtb_ecac_eprocessos_ativos_historico', $this);
        return $this->db->insert_id();
    }
   
    public function qtd_historico($idProcesso, $banco){
        $this->db->select('COUNT(distinct(h.id)) AS qtd'); 
        $this->db->where('h.idProcesso', $idProcesso);

        return $this->db->get($banco.'.dtb_ecac_eprocessos_ativos_historico as h')->row();
    }
}
