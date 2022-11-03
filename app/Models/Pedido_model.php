<?php 
	namespace App\Models;

    use App\Models\EM_model;

	class Pedido_model extends EM_model {

        public function __construct() {
            parent::__construct();
            $this->builder = $this->db->table('tb_pedido');
        }
		
        public function cadastrar($dados) {

            $dadosKeys = array_keys($dados);
            
            for ($i = 0; $i < count($dados); $i++) {
                $maquinas = $dados[$dadosKeys[$i]]['maquinas'];
                unset($dados[$dadosKeys[$i]]['maquinas']);
                
                if($this->builder->insert($dados[$dadosKeys[$i]])) {
                    $pedidoId = $this->db->insertID();
                    $this->salvarMaquinasDoPedido($pedidoId, $maquinas);
                }
            }
        }

        public function atualizar() {

        }

        public function listar($filtros) {
            $sql = 'SELECT 
                        p.*, sp.nm_status_pedido
                    FROM
                        tb_pedido p
                    JOIN tb_status_pedido sp ON sp.cd_status_pedido = p.cd_status_pedido
                    WHERE p.cd_pedido > 0 ';

            if (count($filtros)) {
                if (isset($filtros['cd_cliente'])) $sql .= " AND cli.cd_usuario = ".$filtros['cd_cliente'];
                if (isset($filtros['cd_fornecedor'])) $sql .= " AND f.cd_usuario = ".$filtros['cd_fornecedor'];
                if (is_numeric($filtros['cd_pedido'])) $sql .= " AND p.cd_pedido = ".$filtros['cd_pedido'];   
            }

            // if ($groupBy) $sql .= ' GROUP BY p.cd_pedido ';
            
            $sql .= ' ORDER BY p.cd_pedido DESC';

            // executa a consulta
            $query = $this->db->query($sql);

            // recupera os dados da consulta como Array
            return ObjectToArray($query->getResult());
        }

        public function excluir() {

        }

        public function salvarMaquinasDoPedido($pedidoId, $maquinas) {
            $builder = $this->db->table('tb_pedido_maquina');

            $dataArray = [];

            for ($i = 0; $i < count($maquinas); $i++) {
                $dataArray[] = [
                    'cd_pedido' => $pedidoId,
                    'cd_maquina' => $maquinas[$i]['cd_maquina'],
                    'vl_hora' =>  $maquinas[$i]['vl_hora']
                ];
            }

            return $builder->insertBatch($dataArray);
        }
	}