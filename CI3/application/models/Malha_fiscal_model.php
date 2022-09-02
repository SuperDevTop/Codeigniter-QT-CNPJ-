<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Malha_fiscal_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}


	public function find_emails_nao_enviados($banco){
		$this->db->select('dtb_ecac_caixa_postal_mensagem.id, dtb_ecac_caixa_postal_mensagem.lida as lida, dtb_ecac_caixa_postal_mensagem.id_mensagem as id_mensagem_ecac, dtb_ecac_caixa_postal_mensagem.assunto, e.razao_social, e.cnpj, dtb_ecac_caixa_postal_mensagem.recebida_em, dtb_ecac_caixa_postal_mensagem.conteudo, c.id as caixa_postal_id');
		$this->db->join($banco.'.dtb_ecac_caixa_postal as c', 'dtb_ecac_caixa_postal_mensagem.caixa_postal_id = c.id');
		$this->db->join($banco.'.dtb_empresas as e', 'c.cnpj_data = e.cnpj');
		$this->db->where('dtb_ecac_caixa_postal_mensagem.assunto like "%Malha%" ');
        $this->db->where("dtb_ecac_caixa_postal_mensagem.recebida_em >= '2021-01-01'");
		return $this->db->get($banco.'.dtb_ecac_caixa_postal_mensagem')->result();
	}


	public function insere_mensagem_malha($banco, $id, $data, $assunto, $conteudo, $lida, $cnpj, $razao_social){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

        	    'id_mensagem' => $id,
        	    'data' => $data,
        	    'assunto'=>$assunto,
        	    'conteudo'=>$conteudo,
        	    'lida'=>$lida,
                'cnpj' => $cnpj,
                'razao_social' => $razao_social,
                'data_execucao' => date('Y-m-d H:i:s'),
                'situacao'=>'PENDÊNCIAS',
        );
    
        $this->db->insert($banco.'.dtb_malha_fiscal', $dados);
        return $this->db->insert_id();
    }

    public function update($banco, $id, $data, $assunto, $conteudo, $lida, $cnpj, $razao_social, $id_r){
        date_default_timezone_set('America/Sao_Paulo');

        $dados = array( 

                'id_mensagem' => $id,
                'conteudo'=>$conteudo,
                'lida'=>$lida,
                'data_execucao' => date('Y-m-d H:i:s')
        );
    
    
        if ($this->db->update($banco.'.dtb_malha_fiscal', $dados, "id=".$id_r)){
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public function verifica_se_existe($banco, $cnpj, $id_mensagem, $assunto, $data){
        $this->db->select('COUNT(distinct(dtb_malha_fiscal.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('assunto', $assunto);
        $this->db->where('data', $data);
        $this->db->where('id_mensagem', $id_mensagem);
        return $this->db->get($banco.'.dtb_malha_fiscal')->row();
    }

    public function find_id($banco, $cnpj, $id_mensagem, $assunto, $data){
        $this->db->select('id');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('assunto', $assunto);
        $this->db->where('data', $data);
        $this->db->where('id_mensagem', $id_mensagem);
        return $this->db->get($banco.'.dtb_malha_fiscal')->row();
    }


}