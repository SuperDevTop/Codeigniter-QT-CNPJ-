<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dte_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	public function update($banco, $id, $conteudo, $anexo, $numero_msg){
		$status_bd = 'Mensagem Lida - '.$numero_msg;
		$dados = array(

				'conteudo_mensagem' => $conteudo,
				'caminho_anexo'=> $anexo,
				'status' => $status_bd
		);

		if ($this->db->update($banco.'.dtb_mensagens_dte', $dados, "id=".$id."")){
			return TRUE;
		} else {
			return FALSE;
		}
	}

}