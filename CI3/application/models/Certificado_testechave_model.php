<?php

class Certificado_testechave_model extends CI_Model {

    private $cnpj_data, $cert_key, $pub_key, $pri_key;

    public function setCnpj($cnpj_data){
        $this->cnpj_data = $cnpj_data;
    }

    public function getCnpj(){
        return $this->cnpj_data;
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

	public function get($banco)
	{		
		$query = $this->db->get($banco.'.dtb_certificado');
		return $query->result();
	}

    public function atualizar_chaves($banco){

        $dados = array(
            'cert_key' => $this->getCertKey(),
            'pub_key' => $this->getPubKey(),
            'pri_key' => $this->getPriKey(),
        );

        if ($this->db->update($banco.'.dtb_certificado', $dados, "cnpj_data='{$this->getCnpj()}'")){
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
