<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Caixadeemail_model extends CI_Model {

	private $id;
	private $cnpj;
	private $razao_social;
	private $id_empresa;
	private $titulo;
	private $mensagem;
	private $status;
	private $remetente;
	private $dataemail;
	private $importante;
	private $id_contabilidade;
	
	function __construct()
	{
		parent::__construct();
	}

	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function getCnpj() {
		return $this->cnpj;
	}
	public function setCnpj($cnpj) {
		$this->cnpj = $cnpj;
		return $this;
	}

	public function getRazaoSocial() {
		return $this->razao_social;
	}
	public function setRazaoSocial($razao_social) {
		$this->razao_social = $razao_social;
		return $this;
	}

	public function getIdEmpresa() {
		return $this->id_empresa;
	}
	public function setIdEmpresa($id_empresa) {
		$this->id_empresa = $id_empresa;
		return $this;
	}

	public function getTitulo() {
		return $this->titulo;
	}
	public function setTitulo($titulo) {
		$this->titulo = $titulo;
		return $this;
	}

	public function getMensagem() {
		return $this->mensagem;
	}
	public function setMensagem($mensagem) {
		$this->mensagem = $mensagem;
		return $this;
	}

	public function getStatus() {
		return $this->status;
	}
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}

	public function getRemetente() {
		return $this->remetente;
	}
	public function setRemetente($remetente) {
		$this->remetente = $remetente;
		return $this;
	}

	public function getDataEmail() {
		return $this->dataemail;
	}
	public function setDataEmail($dataemail) {
		$this->dataemail = $dataemail;
		return $this;
	}

	public function getImportante() {
		return $this->importante;
	}
	public function setImportante($importante) {
		$this->importante = $importante;
		return $this;
	}

	public function getIdContabilidade() {
		return $this->id_contabilidade;
	}
	public function setIdContabilidade($id_contabilidade) {
		$this->id_contabilidade = $id_contabilidade;
		return $this;
	}
	
	public function find_empresa_email($cnpj, $banco){
		$this->db->select('id, razao_social');
		$this->db->where("cnpj_completo", $cnpj);
		$this->db->group_by('id');
		return $this->db->get($banco.'.dtb_empresas')->row();

	}


	public function inserir_msg_nova($cnpj, $id_empresa, $razao_social, $titulo, $mensagem, $status, $id_contabilidade, $remetente, $dataemail, $banco){

		$dados = array(

				'cnpj' => $cnpj,
				'id_empresa' => $id_empresa,
				'razao_social' => $razao_social,
				'titulo' => $titulo,
				'mensagem' => $mensagem,
				'status' => $status,
				'id_contabilidade' => $id_contabilidade,
				'remetente' => $remetente,
				'dataemail' => $dataemail
		);

		if ($this->db->insert($banco.'.dtb_caixadeemail', $dados)){
			return;
		}

	}
	
	public function pesquisar_plano_id($banco){
		$this->db->select('*');
		$this->db->where('id', 1);
		return $this->db->get($banco.'.dtb_plano_contratado')->row();
	}

}