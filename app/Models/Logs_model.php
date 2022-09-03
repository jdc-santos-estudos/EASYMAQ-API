<?php 
	namespace App\Models;

	use CodeIgniter\Model;

	class Logs_model extends Model {
    
    protected $builder;
    protected $allowedFields = [];

    public function __construct() {
      parent::__construct();
      $this->builder = $this->db->table('tb_logs');
    }

    public function salvarLog($data) {
      $res = $this->builder->insert($data);

      return $res;
    }

    public function getLogs() {
      $query = $this->builder->get();

      $data = [];

      foreach ($query->getResult() as $row) $data[] = json_decode(json_encode($row), true);;

      return $data;
    }
	}