<?php 
	namespace App\Models;

	use App\Models\EM_model;

	class Estado_model extends EM_model {
    
    public function __construct() {
      parent::__construct();
      $this->builder = $this->db->table('tb_estado');
    }
		
    public function listar() {
      $query = $this->builder->get();
      return ObjectToArray($query->getResult());
    }
	}