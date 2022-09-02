<?php

class Relp_mei_debitos_parcelas_model extends CI_Model {

    public $id;
    public $id_parcelamento;
    public $cnpj;
    public $periodo_apuracao;
    public $vencimento;
    public $numero_processo;
    public $saldo_devedor_original;
    public $valor_atualizado;

    public function clear($cnpj, $banco){
        return $this->db->delete($banco.'.dtb_ecac_relp_mei_relacao_debitos_parcelas', "cnpj = '{$cnpj}'");
    }

    public function insert($dados, $banco){
        date_default_timezone_set('America/Sao_Paulo');
        $this->id_parcelamento = $dados['id_parcelamento'];
        $this->cnpj = $dados['cnpj'];
        $this->periodo_apuracao = $dados['periodo_apuracao'];
        $this->vencimento = $dados['vencimento'];
        $this->numero_processo = $dados['numero_processo'];
        $this->saldo_devedor_original = $dados['saldo_devedor_original'];
        $this->valor_atualizado = $dados['valor_atualizado'];

        $this->db->insert($banco.'.dtb_ecac_relp_mei_relacao_debitos_parcelas', $this);
        return $this->db->insert_id();
    }

    public function verifica_se_existe($cnpj, $banco, $id_parcelamento){
        $this->db->select('COUNT(distinct(dtb_ecac_relp_mei_relacao_debitos_parcelas.id)) AS qtd');
        $this->db->where('cnpj', $cnpj);
        $this->db->where('id_parcelamento', $id_parcelamento);
        return $this->db->get($banco.'.dtb_ecac_relp_mei_relacao_debitos_parcelas')->row();
    }
}