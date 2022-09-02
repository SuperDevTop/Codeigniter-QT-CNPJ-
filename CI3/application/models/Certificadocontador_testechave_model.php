<?php

class Certificadocontador_testechave_model extends CI_Model {
    private $id, $cert_key, $pub_key, $pri_key, $cnpj;

	public function get($banco)
	{		
		$query = $this->db->get($banco.'.dtb_certificado_contador');
		return $query->result();
	}

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setCertKey($cert_key){
        $this->cert_key = $cert_key;
    }

    public function getCertKey(){
        return $this->cert_key;
    }

    public function setPubKey($pub_key){
        $this->pub_key = $pub_key;
    }

    public function getPubKey(){
        return $this->pub_key;
    }

    public function setPriKey($pri_key){
        $this->pri_key = $pri_key;
    }

    public function getPriKey(){
        return $this->pri_key;
    }

    public function setCnpj($cnpj){
        $this->cnpj = $cnpj;
    }

    public function getCnpj(){
        return $this->cnpj;
    }

    public function atualizar_chaves($banco){

        $dados = array(
            'cert_key' => $this->getCertKey(),
            'pub_key' => $this->getPubKey(),
            'pri_key' => $this->getPriKey(),
            'cnpj_data' => $this->getCnpj(),
        );
        if ($this->db->update($banco.'.dtb_certificado_contador', $dados, "id={$this->getId()}")){
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
