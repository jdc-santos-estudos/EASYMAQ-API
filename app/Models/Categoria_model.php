<?php 
namespace App\Models;

use App\Models\EM_model;

class Categoria_model extends EM_model {
		
        public function __construct() {
                parent::__construct();
                $this->builder = $this->db->table('tb_categoria');
             
        }

        public function cadastrar($dados) {
                return $this->builder->insert($dados);

        }

        public function atualizar($cd_categoria, $nm_categoria) {
                $sql = " UPDATE tb_categoria SET nm_categoria = '{$nm_categoria}' WHERE cd_categoria = {$cd_categoria}";
                return $this ->db->query($sql);

        }

        public function listar($cd_categoria=null, $nm_categoria=null) {
                $where = "";

                if($cd_categoria != null || $nm_categoria != null ){
                  $where = "WHERE ";

                  if($cd_categoria != null){
                        $where .= 'cd_categoria = '. $cd_categoria.' ';
                  }

                  if($nm_categoria != null){
                        $where .= 'nm_categoria LIKE "%'. $nm_categoria.'%" ';
                  }
                }
                $sql = "SELECT * FROM tb_categoria ". $where;

        
                // executa a consulta
                $query = $this->db->query($sql);

                // recupera os dados da consulta como Array
                $res = ObjectToArray($query->getResult());
                return $res;

        }

        public function excluir($cd) {
                $sql="delete from tb_categoria where cd_categoria = ".$cd;
                return $this->db->query($sql); 
        }
}