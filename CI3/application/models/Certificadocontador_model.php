<?php

class Certificadocontador_model extends CI_Model {
    private $id, $cert_key, $pub_key, $pri_key, $cnpj_data;

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
        $this->cnpj_data = $cnpj;
    }

    public function getCnpj(){
        return $this->cnpj_data;
    }

    public function get_empresas_ecac_ativa($banco, $id_contador){
        $this->db->select('distinct(e.cnpj)');
        $this->db->join($banco.'.dtb_empresas as e', 'e.cnpj = p.cnpj_outorgante', 'inner');
        $this->db->join($banco.'.dtb_certificado_contador as cc', 'cc.cnpj_data = p.cnpj', 'inner');
        $this->db->where('p.situacao', 'Ativa');
        $this->db->where('cc.id_contador', $id_contador);
        return $this->db->get($banco.'.dtb_ecac_procuracao as p')->result();
    }

    public function atualizar_chaves($banco){

        $dados = array(
            'cert_key' => $this->getCertKey(),
            'pub_key' => $this->getPubKey(),
            'pri_key' => $this->getPriKey(),
            'cnpj_data' => $this->getCnpj()
        );
        if ($this->db->update($banco.'.dtb_certificado_contador', $dados, "id={$this->getId()}")){
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
