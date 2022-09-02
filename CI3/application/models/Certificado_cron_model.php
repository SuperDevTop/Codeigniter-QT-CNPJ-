<?php

class Certificado_cron_model extends CI_Model {

	public function get($banco)
	{		
		return $this->db->get($banco.'.dtb_certificado')->result();
	}

	public function get_extra($banco)
	{		
		$this->db->where('dtb_certificado.id_empresa >', 250);
        $this->db->where('dtb_certificado.id_empresa <=', 500);
		return $this->db->get($banco.'.dtb_certificado')->result();
	}

	public function get_extra_2($banco)
	{		
		$this->db->where('dtb_certificado.id_empresa >', 500);
        $this->db->where('dtb_certificado.id_empresa <=', 750);
		return $this->db->get($banco.'.dtb_certificado')->result();
	}

	public function get_extra_3($banco)
	{		
		$this->db->where('dtb_certificado.id_empresa >', 750);
        $this->db->where('dtb_certificado.id_empresa <=', 900);
		return $this->db->get($banco.'.dtb_certificado')->result();
	}

	public function get_extra_4($banco)
	{		
		$this->db->where('dtb_certificado.id_empresa >', 900);
		return $this->db->get($banco.'.dtb_certificado')->result();
	}
}
