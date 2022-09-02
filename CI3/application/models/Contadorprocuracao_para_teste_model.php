<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contadorprocuracao_para_teste_model extends CI_Model {

    private $id;
    private $id_empresa;
    private $id_contador;

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

    public function getIdEmpresa() {
        return $this->id_empresa;
    }
    public function setIdEmpresa($id_empresa) {
        $this->id_empresa = $id_empresa;
        return $this;
    }

    public function getIdContador() {
        return $this->id_contador;
    }
    public function setIdContador($id_contador) {
        $this->id_contador = $id_contador;
        return $this;
    }

    public function findAllByIdEmpresa() {
        $this->db->select('id_contador as id');
        $this->db->where('id_empresa', $this->getIdEmpresa());

        return $this->db->get('dtb_contador_procuracao')->result();
    }

    public function buscar_empresas_vinculadas($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        // $this->db->where('dbtc.id_empresa <=', 100);
        $this->db->where('e.cnpj', '40335016000170');
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_1($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 100);
        $this->db->where('dbtc.id_empresa <=', 200);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_2($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 200);
        $this->db->where('dbtc.id_empresa <=', 300);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_3($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 300);
        $this->db->where('dbtc.id_empresa <=', 400);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_4($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 400);
        $this->db->where('dbtc.id_empresa <=', 500);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_5($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 500);
        $this->db->where('dbtc.id_empresa <=', 600);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_6($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 600);
        $this->db->where('dbtc.id_empresa <=', 700);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_7($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 700);
        $this->db->where('dbtc.id_empresa <=', 800);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_8($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 800);
        $this->db->where('dbtc.id_empresa <=', 900);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_9($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 900);
        $this->db->where('dbtc.id_empresa <=', 1000);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_10($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1000);
        $this->db->where('dbtc.id_empresa <=', 1100);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_11($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1100);
        $this->db->where('dbtc.id_empresa <=', 1200);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_12($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1200);
        $this->db->where('dbtc.id_empresa <=', 1300);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    } 

    public function buscar_empresas_vinculadas_extra_13($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1300);
        $this->db->where('dbtc.id_empresa <=', 1400);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_14($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1400);
        $this->db->where('dbtc.id_empresa <=', 1500);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_15($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1500);
        $this->db->where('dbtc.id_empresa <=', 1600);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_16($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 1600);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    } 

    // public function buscar_empresas_vinculadas_extra_16($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_17($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_18($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_19($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_20($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_21($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_22($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_23($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_24($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_25($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_26($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_27($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_28($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_29($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_30($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_31($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_32($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_33($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_34($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_35($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_36($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_37($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_38($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_39($banco, $id){
    //      $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_situacao_fiscal as s', 'e.cnpj = s.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , s.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_40($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_41($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_42($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_43($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_44($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_45($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_46($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_47($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_48($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_49($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    // public function buscar_empresas_vinculadas_extra_50($banco, $id){
    //     $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
    //     $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
    //     $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
    //     $this->db->where('dbtc.id_contador', $id);
    //     $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 24 ');
    //     $this->db->order_by('dbtc.id_empresa asc');

    //     return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    // }

    public function buscar_empresas_vinculadas_extra_51($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->join($banco.'.dtb_situacao_fiscal as ec', 'e.cnpj = ec.cnpj_data');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 8 ');
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_nao_rodou($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('TIMESTAMPDIFF(HOUR , ec.data_execucao, now()) > 12 ');
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_todas($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_validar($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa', 4);

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

     public function buscar_empresas_vinculadas_teste($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('e.cnpj like "%0001%" ');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_dec($banco, $id){
        
        $this->db->select($banco.'.dtb_contador_procuracao_dec_sp.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dtb_contador_procuracao_dec_sp.id_empresa = e.id');
        $this->db->where($banco.'.dtb_contador_procuracao_dec_sp.id_contador', $id);

        return $this->db->get($banco.'.dtb_contador_procuracao')->result();
    }

    public function buscar_empresas_vinculadas_nao_atualizou_mensagens($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->join($banco.'.dtb_ecac_caixa_postal as ec', 'e.cnpj = ec.cnpj_data');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('DATE(ec.data_execucao) < CURRENT_DATE()');
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_nao_atualizou_diagnostico($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->join($banco.'.dtb_situacao_fiscal as ec', 'e.cnpj = ec.cnpj_data');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('DATE(ec.data_execucao) < CURRENT_DATE()');
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    } 

    public function buscar_empresas_vinculadas_extra_mais($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa <=', 250);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }


     public function buscar_empresas_nao_pegou($banco, $id){

        $this->db->select('e.id, e.razao_social, e.cnpj');
        $this->db->where('e.cnpj not in (select cnpj_data from dtb_situacao_fiscal)');

        return $this->db->get($banco.'.dtb_empresas as e')->result();
    }

    public function buscar_empresas_vinculadas_extra_mais_extra($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 250);
        $this->db->order_by('dbtc.id_empresa asc');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function insere_empresas_sem_procuracao($banco, $cnpj){
        $dados = array( 
                'cnpj' => $cnpj
        );
        
        if ($this->db->insert($banco.'.dtb_empresas_sem_procuracao', $dados)){
            return $this->db->insert_id();
        } else {
            return FALSE;
        }

    }

    public function clear_table($banco){
        $this->db->truncate($banco.'.dtb_empresas_sem_procuracao');
    }

    public function buscar_empresa($id, $cnpj, $banco){
        $this->db->select('dtb_contador_procuracao.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dtb_contador_procuracao.id_empresa = e.id');
        $this->db->where('dtb_contador_procuracao.id_contador', $id);
        $this->db->where('e.cnpj', preg_replace("/[^0-9]/", "", $cnpj));

        return $this->db->get($banco.'.dtb_contador_procuracao')->row();
    }

}