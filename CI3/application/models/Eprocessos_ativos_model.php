<?php

class Eprocessos_ativos_model extends CI_Model {

    public $cnpj;
    public $idProcesso;
    public $dataProtocolo;
    public $dataProtocoloFormatada;
    public $grupo;
    public $indicadorVersaoProcesso;
    public $localizacao;
    public $natureza;
    public $niInteressado;
    public $niInteressadoFormatado;
    public $nomeInteressado;
    public $numero;
    public $numeroFormatado;
    public $numeroProcessoProcedimento;
    public $numeroProcessoProcedimentoFormatado;
    public $permiteVisualizarProcesso;
    public $situacao;
    public $situacaoSolidario;
    public $subtipo;
    public $tipo;
    public $tipoResponsabilidade;

	public function clear($banco){
		return $this->db->truncate($banco.'.dtb_ecac_eprocessos_ativos');
	}

	public function insert($cnpj, $processo, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$this->cnpj = $cnpj;
		$this->idProcesso = $processo['id'];
        $this->dataProtocolo = $processo['dataProtocolo'];
        $this->dataProtocoloFormatada = $processo['dataProtocoloFormatada'];
        $this->grupo = $processo['grupo'];
        $this->indicadorVersaoProcesso = $processo['indicadorVersaoProcesso'];
        $this->localizacao = $processo['localizacao'];
        $this->natureza = $processo['natureza'];
        $this->niInteressado = $processo['niInteressado'];
        $this->niInteressadoFormatado = $processo['niInteressadoFormatado'];
        $this->nomeInteressado = $processo['nomeInteressado'];
        $this->numero = $processo['numero'];
        $this->numeroFormatado = $processo['numeroFormatado'];
        $this->numeroProcessoProcedimento = $processo['numeroProcessoProcedimento'];
        $this->numeroProcessoProcedimentoFormatado = $processo['numeroProcessoProcedimentoFormatado'];
        $this->permiteVisualizarProcesso = $processo['permiteVisualizarProcesso'];
        $this->situacao = $processo['situacao'];
        $this->situacaoSolidario = $processo['situacaoSolidario'];
        $this->subtipo = $processo['subtipo'];
        $this->tipo = $processo['tipo'];
        $this->tipoResponsabilidade = $processo['tipoResponsabilidade'];

		$this->db->insert($banco.'.dtb_ecac_eprocessos_ativos', $this);
		return $this->db->insert_id();
	}

}
