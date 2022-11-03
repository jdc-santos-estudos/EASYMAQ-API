<?php 
	namespace App\Models;

	use App\Models\EM_model;

	class Cidade_model extends EM_model {
    
    public function __construct() {
      parent::__construct();
      $this->builder = $this->db->table('tb_cidade');
    }
		
    public function listar($cd_estado) {
      $this->builder->where('cd_estado', $cd_estado);
      $query = $this->builder->get();
      return ObjectToArray($query->getResult());
    }
	}