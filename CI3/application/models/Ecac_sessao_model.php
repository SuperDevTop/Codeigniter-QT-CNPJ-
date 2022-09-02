<?php

class Ecac_sessao_model extends CI_Model {

    public function atualizar($banco, $cnpj, $cookiecav, $aspsession){

        $dados = array(
            'cnpj' => $cnpj,
            'cookiecav' => $cookiecav,
            'aspsession' => $aspsession,
        );

        $this->db->select('*');
        $this->db->where('cnpj', $cnpj);
        if (!$this->db->get($banco.'.dtb_ecac_sessoes')->row())
            $this->db->insert($banco.'.dtb_ecac_sessoes', $dados);
        else
            $this->db->update($banco.'.dtb_ecac_sessoes', $dados, "cnpj='{$cnpj}'");
    }

    public function find_by_cnpj($banco, $cnpj){
        $DB = $this->load->database('sessao', TRUE);
        $DB->select('*');
        $DB->where('cnpj', $cnpj);
        return $DB->get('dtb_sessoes')->row();
    }
}
