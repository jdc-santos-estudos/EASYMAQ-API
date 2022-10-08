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

      // se nao possuir o campo id, insere o novo registro
      if (! isset($data['cd_configuracao'])) return $this->builder->insert($data);

      $id = $data['cd_configuracao'];
      unset($data['cd_configuracao']);      
      
      $builder->where('cd_configuracao', $id);

      return $builder->update([
        'nm_config' => $data['nm_config'],
        'ds_config' => $data['ds_config'],
        'ds_valor'  => $data['ds_valor'],
      ]);
    }

    public function listar() {
      $query = $this->builder->get();
      return ObjectToArray($query->getResult());
    }

    public function getConfigFront($version) {
      $version = addslashes($version);

      $query = $this->db->query('SELECT * FROM tb_configuracao WHERE nm_config = "CONFIG_FRONT"');

      $res = ObjectToArray($query->getResult());

      if (is_array($res) && count($res)) return (json_decode(json_decode($res[0]['ds_valor']),1)[$version]);
    }
	}