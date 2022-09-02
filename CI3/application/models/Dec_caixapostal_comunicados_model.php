<?php

class Dec_caixapostal_comunicados_model extends CI_Model {

    public  $cnpj;
    public  $assunto ;
    public  $complemento ;
    public  $data_envio ;
    public  $path_anexo ;
    public  $lida ;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_dec_caixapostal_comunicados', "cnpj = {$cnpj}");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $dados['cnpj'];
        $this->assunto = $dados['assunto'];
        $this->complemento = $dados['complemento'];
        $this->data_envio = $dados['data_envio'];
        $this->lida = $dados['lida'];

        $this->db->insert($banco.'.dtb_dec_caixapostal_comunicados', $this);
        return $this->db->insert_id();
    }

    public function findById($id, $banco) {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get($banco.'.dtb_dec_caixapostal_comunicados')->row();
    }

    public function update_lido($banco, $id, $pathAnexo){
        $banco = $banco . '.dtb_dec_caixapostal_comunicados';
        $sql = "UPDATE {$banco} set lida = 1, path_anexo = '{$pathAnexo}' WHERE id = ".$id;

        $this->db->query($sql);
    }

}
