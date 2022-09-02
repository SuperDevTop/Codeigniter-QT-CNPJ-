<?php

class Dctf_model extends CI_Model {

    public $cnpj;
    public $cnpj_formatado;
    public $periodo;
    public $periodo_inicial;
    public $periodo_final;
    public $situacao;
    public $tipo_status;
    public $numero_declaracao;
    public $numero_recibo;
    public $data_recepcao;
    public $data_processamento;
    public $caminho_download_declaracao;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_dctf', "cnpj = {$cnpj}");
    }

	public function insert($dctf, $banco){
		date_default_timezone_set('America/Sao_Paulo');
		$this->cnpj = $dctf['cnpj'];
        $this->cnpj_formatado = $dctf['cnpj_formatado'];
        $this->periodo = $dctf['periodo'];
        $this->periodo_inicial = $dctf['periodo_inicial'];
        $this->periodo_final = $dctf['periodo_final'];
        $this->situacao = $dctf['situacao'];
        $this->tipo_status = $dctf['tipo_status'];
        $this->data_recepcao = $dctf['data_recepcao'];
        $this->numero_declaracao = isset($dctf['numero_declaracao']) ? $dctf['numero_declaracao'] : '';
        $this->numero_recibo = isset($dctf['numero_recibo']) ? $dctf['numero_recibo'] : '';
        $this->data_processamento = isset($dctf['data_processamento']) ? $dctf['data_processamento'] : $dctf['data_processamento'];
        //$this->caminho_download_declaracao = isset($dctf['caminho_download_declaracao']) ? $dctf['caminho_download_declaracao'] : '';

		$this->db->insert($banco.'.dtb_ecac_dctf', $this);
		return $this->db->insert_id();
	}

    public function update_caminho_download($caminho_download , $periodo, $cnpj, $banco){
        $this->db->set('caminho_download_declaracao', $caminho_download);
        $this->db->where('periodo', $periodo);
        $this->db->where('cnpj', $cnpj);
        $this->db->update($banco.'.dtb_ecac_dctf');
    }

    //Funções auxiliares na busca por certificados por procuração ou individual
    public function find_certificado($cnpj, $banco){
        $this->db->select('*');
        $this->db->where('cnpj_data', $cnpj);
        return $this->db->get($banco.'.dtb_certificado')->row();
    }

    public function get_aux($id, $banco)
    {       
        $this->db->select('*');
        $this->db->join($banco.'.dtb_contador_procuracao d','db.id_contador = d.id_contador', 'left');
        $this->db->where('d.id_empresa', $id);
        return $this->db->get($banco.'.dtb_certificado_contador db')->result();
    }

    public function find_empresa_by_cnpj($banco, $cnpj){
        $this->db->select('id');
        $this->db->where("cnpj", $cnpj);

        return $this->db->get($banco.'.dtb_empresas')->row();
    }

    //Fim das funçoes auxiliares

    public function find_all_dctf($banco)
    {       
        $this->db->select('*');
        $this->db->where('tipo_status not like "%Cancelada%" ');
        return $this->db->get($banco.'.dtb_ecac_dctf')->result();
    }
}
