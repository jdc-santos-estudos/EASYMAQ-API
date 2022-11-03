<?php 
	namespace App\Models;

	use CodeIgniter\Model;

	class Configuracao_model extends Model {
    
    protected $builder;

    public function __construct() {
      parent::__construct();
      $this->builder = $this->db->table('tb_configuracao');
    }

    public function salvar($data) {
      $this->builder->where('nm_config', $data['config']);

      return $this->builder->update([
        'ds_valor'  => addslashes(json_encode($data['dados'])),
      ]);
    }

    public function listar() {
      $query = $this->builder->get();
      return ObjectToArray($query->getResult());
    }

    public function getConfig($dados) {
      $sql = "SELECT * FROM tb_configuracao";
      if ($dados['config']) $sql .= " WHERE nm_config = '".$dados['config']."'";
      
      $res = $this->db->query($sql);

      return ObjectToArray($res->getResult());
    }
	}