<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contadorprocuracao_contas_model extends CI_Model {

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

    public function buscar_empresas_vinculadas_teste($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id', 'left');
        $this->db->where('dbtc.id_contador', $id);
        // $this->db->where('dbtc.id_empresa <=', 500);
        $this->db->where('e.cnpj', '03598842000185');

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id', 'left');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa <=', 500);

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id', 'left');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 500);

        return $this->db->get($banco.'.dtb_contador_procuracao as dbtc')->result();
    }

    public function buscar_empresas_vinculadas_extra_mais($banco, $id){
        $this->db->select('dbtc.id_empresa, e.razao_social, e.cnpj');
        $this->db->join($banco.'.dtb_empresas as e', 'dbtc.id_empresa = e.id', 'left');
        $this->db->where('dbtc.id_contador', $id);
        $this->db->where('dbtc.id_empresa >', 900);

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

}