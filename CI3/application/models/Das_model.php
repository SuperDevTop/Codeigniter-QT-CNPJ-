<?php

class Das_model extends CI_Model
{

    public $id;
    public $cnpj;
    public $compentencia;
    public $numero_declaracao;
    public $data_hora_transmissao;
    public $numero_das;
    public $data_hora_emissao;
    public $pago;
    public $caminho_download_recibo;
    public $caminho_download_declaracao;
    public $caminho_download_extrato;

    public function get_all($banco, $cnpj = '')
    {
        $this->db->select('*');
        if ($cnpj != '')
            $this->db->where('cnpj', $cnpj);
        $this->db->like('compentencia', date('Y'), 'both');

        return $this->db->get($banco . '.dtb_ecac_das')->result();
    }

    public function find_by_numero_declaracao($numero_declaracao, $banco)
    {
        $this->db->select('*');
        $this->db->where('numero_declaracao', $numero_declaracao);
        return $this->db->get($banco . '.dtb_ecac_das')->row();
    }

    public function verifica_se_existe($numero_declaracao, $banco, $cnpj, $numero_das)
    {
        $this->db->select('COUNT(distinct(dtb_ecac_das.id)) AS qtd , dtb_ecac_das.caminho_download_das, dtb_ecac_das.caminho_download_recibo , dtb_ecac_das.caminho_download_declaracao, dtb_ecac_das.caminho_download_extrato');
        $this->db->where('numero_declaracao', $numero_declaracao);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('numero_das', $numero_das);
        return $this->db->get($banco . '.dtb_ecac_das')->row();
    }

    public function verifica_se_existe_caminho_download_das($banco, $cnpj, $compentencia){
        $this->db->select('COUNT(distinct(dtb_ecac_das.id)) AS qtd , dtb_ecac_das.caminho_download_das');
        $this->db->where('compentencia', $compentencia);
        $this->db->where('cnpj', $cnpj);
        $where = "caminho_download_das is  NOT NULL";
        $this->db->where($where);
        return $this->db->get($banco.'.dtb_ecac_das')->row();
    }
    
    public function verifica_se_pago($banco, $cnpj, $compentencia)
    {
        $this->db->select('COUNT(distinct(dtb_ecac_das.id)) AS qtd');
        $this->db->where('compentencia', $compentencia);
        $this->db->where('cnpj', $cnpj);
        $this->db->where('pago', 'Sim');
              
        return $this->db->get($banco . '.dtb_ecac_das')->row();
    }

    public function insert($dados, $banco)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $this->cnpj = $dados['cnpj'];
        $this->compentencia = $dados['compentencia'];
        $this->numero_declaracao = $dados['numero_declaracao'];
        $this->data_hora_transmissao = $dados['data_hora_transmissao'];
        $this->numero_das = $dados['numero_das'];
        $this->data_hora_emissao = $dados['data_hora_emissao'];
        $this->pago = $dados['pago'];


        $this->db->insert($banco . '.dtb_ecac_das', $this);
        return $this->db->insert_id();
    }

    public function update($dados, $banco)
    {
        $atualizou = false;

        if (isset($dados['compentencia']) && !empty($dados['compentencia'])) {
            $this->db->set('compentencia', $dados['compentencia']);
            $atualizou = true;
        }
        if (isset($dados['numero_declaracao']) && !empty($dados['numero_declaracao'])) {
            $this->db->set('numero_declaracao', $dados['numero_declaracao']);
            $atualizou = true;
        }
        if (isset($dados['data_hora_transmissao']) && !empty($dados['data_hora_transmissao'])) {
            $this->db->set('data_hora_transmissao', $dados['data_hora_transmissao']);
            $atualizou = true;
        }
        if (isset($dados['numero_das']) && !empty($dados['numero_das'])) {
            $this->db->set('numero_das', $dados['numero_das']);
            $atualizou = true;
        }
        if (isset($dados['data_hora_emissao']) && !empty($dados['data_hora_emissao'])) {
            $this->db->set('data_hora_emissao', $dados['data_hora_emissao']);
            $atualizou = true;
        }
        if (isset($dados['pago']) && !empty($dados['pago'])) {
            $this->db->set('pago', $dados['pago']);
            $atualizou = true;
        }
        if ($atualizou) {
            $this->db->where('numero_declaracao', $dados['numero_declaracao']);
            if (isset($dados['numero_das']) && !empty($dados['numero_das']))
                $this->db->where('numero_das', $dados['numero_das']);

            $this->db->update($banco . '.dtb_ecac_das');
        }
    }

    public function update_anexos($dados, $banco)
    {
        $atualizou = false;
        if (isset($dados['caminho_download_recibo']) && !empty($dados['caminho_download_recibo'])) {
            $this->db->set('caminho_download_recibo', $dados['caminho_download_recibo']);
            $atualizou = true;
        }
        if (isset($dados['caminho_download_declaracao']) && !empty($dados['caminho_download_declaracao'])) {
            $this->db->set('caminho_download_declaracao', $dados['caminho_download_declaracao']);
            $atualizou = true;
        }
        if (isset($dados['caminho_download_extrato']) && !empty($dados['caminho_download_extrato'])) {
            $this->db->set('caminho_download_extrato', $dados['caminho_download_extrato']);
            $atualizou = true;
        }
        if ($atualizou) {
            $this->db->where('numero_declaracao', $dados['numero_declaracao']);
            $this->db->update($banco . '.dtb_ecac_das');
        }
    }

    public function update_caminho_download_das($caminho_download, $numero_declaracao, $banco)
    {
        $this->db->set('caminho_download_das', $caminho_download);
        $this->db->where('numero_declaracao', $numero_declaracao);
        $this->db->update($banco . '.dtb_ecac_das');
    }

    public function update_caminho_download_extrato($caminho_download, $numero_declaracao, $banco)
    {
        $this->db->set('caminho_download_extrato', $caminho_download);
        $this->db->where('numero_declaracao', $numero_declaracao);
        $this->db->update($banco . '.dtb_ecac_das');
    }
    public function update_caminho_download_recibo($caminho_download, $numero_declaracao, $banco)
    {
        $this->db->set('caminho_download_recibo', $caminho_download);
        $this->db->where('numero_declaracao', $numero_declaracao);
        $this->db->update($banco . '.dtb_ecac_das');
    }
    public function update_caminho_download_declaracao($caminho_download, $numero_declaracao, $banco)
    {
        $this->db->set('caminho_download_declaracao', $caminho_download);
        $this->db->where('numero_declaracao', $numero_declaracao);
        $this->db->update($banco . '.dtb_ecac_das');
    }


    //Funções auxiliares na busca por certificados por procuração ou individual
    public function find_certificado($cnpj, $banco)
    {
        $this->db->select('*');
        $this->db->where('cnpj_data', $cnpj);
        return $this->db->get($banco . '.dtb_certificado')->row();
    }

    public function get_aux($id, $banco)
    {
        $this->db->select('*');
        $this->db->join($banco . '.dtb_contador_procuracao d', 'db.id_contador = d.id_contador', 'left');
        $this->db->where('d.id_empresa', $id);
        return $this->db->get($banco . '.dtb_certificado_contador db')->result();
    }

    public function find_empresa_by_cnpj($banco, $cnpj)
    {
        $this->db->select('id');
        $this->db->where("cnpj", $cnpj);

        return $this->db->get($banco . '.dtb_empresas')->row();
    }

    public function get_numero_declaracoes($banco, $cnpj)
    {
        $this->db->select('numero_declaracao');
        $this->db->where('cnpj', $cnpj);
        return $this->db->get($banco . '.dtb_ecac_das')->result();
    }

    public function find_all_cert($banco)
    {
        $this->db->select('*');
        return $this->db->get($banco . '.dtb_certificado_contador db')->result();
    }

    public function buscar_empresas_vinculadas($banco, $id)
    {
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco . '.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa <=', 50);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco . '.dtb_contador_procuracao as dbtc')->result();
    }

    //Fim das funçoes auxiliares
}
