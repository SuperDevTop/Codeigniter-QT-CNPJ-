<?php

class Dec_caixapostal_mensagens_model extends CI_Model {

    public  $cnpj;
    public  $identificacao ;
    public  $categoria ;
    public  $assunto ;
    public  $data_envio ;
    public  $data_ciencia ;
    public  $path_anexo ;
    public  $lida ;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_dec_caixapostal_mensagens', "cnpj = {$cnpj}");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');

        $this->cnpj = $dados['cnpj'];
        $this->identificacao = $dados['identificacao'];
        $this->categoria = $dados['categoria'];
        $this->assunto = $dados['assunto'];
        $this->data_envio = $dados['data_envio'];
        $this->data_ciencia = $dados['data_ciencia'];
        $this->lida = $dados['lida'];

        $this->db->insert($banco.'.dtb_dec_caixapostal_mensagens', $this);
        return $this->db->insert_id();
    }

    public function findById($id, $banco) {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get($banco.'.dtb_dec_caixapostal_mensagens')->row();
    }

    public function update_lido($banco, $id, $pathAnexo){
        $banco = $banco . '.dtb_dec_caixapostal_mensagens';
        $sql = "UPDATE {$banco} set lida = 1, path_anexo = '{$pathAnexo}' WHERE id = ".$id;

        $this->db->query($sql);
    }
}
