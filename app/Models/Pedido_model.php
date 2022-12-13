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
                        p.*, sp.nm_status_pedido,
                        cli.nm_usuario as nm_cliente, cli.nm_razao_social as nm_razao_social_cliente, cli.nm_fantasia as nm_fantasia_cliente,
                        cli.cd_rg as cd_rg_cliente, cli.cd_cpf as cd_cpf_cliente, cli.cd_cnpj as cd_cnpj_cliente,
                        f.nm_usuario as nm_fornecedor, f.nm_razao_social as nm_razao_social_fornecedor, f.nm_fantasia as nm_fantasia_fornecedor,
                        cli.ds_email as ds_email_cliente, f.ds_email as ds_email_fornecedor,
                        f.cd_rg as cd_rg_fornecedor, f.cd_cpf as cd_cpf_fornecedor, f.cd_cnpj as cd_cnpj_fornecedor,
                        c.nm_cidade, e.ds_sigla
                    FROM
                        tb_pedido p
                    JOIN tb_status_pedido sp ON sp.cd_status_pedido = p.cd_status_pedido
                    JOIN tb_usuario cli ON cli.cd_usuario = p.cd_usuario 
                    JOIN tb_pedido_maquina pm ON pm.cd_pedido = p.cd_pedido 
                    JOIN tb_maquina m ON m.cd_maquina = pm.cd_maquina 
                    JOIN tb_usuario f ON f.cd_usuario = m.cd_fornecedor
                    JOIN tb_cidade c ON c.cd_cidade = p.cd_cidade
                    JOIN tb_estado e ON e.cd_estado = c.cd_estado
                    WHERE p.cd_pedido > 0 ';

            if (count($filtros)) {
                if (isset($filtros['cd_cliente'])) $sql .= " AND cli.cd_usuario = ".$filtros['cd_cliente'];
                if (isset($filtros['cd_fornecedor'])) $sql .= " AND f.cd_usuario = ".$filtros['cd_fornecedor'];
                if (is_numeric($filtros['cd_pedido'])) $sql .= " AND p.cd_pedido = ".$filtros['cd_pedido'];   
            }

            $sql .= ' GROUP BY p.cd_pedido ';
            
            $sql .= ' ORDER BY p.cd_pedido DESC';

            // echo $sql;
            // exit;

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

        public function atualizarStatus($dados) {
            $sql = 'SELECT * FROM tb_status_pedido WHERE nm_status_pedido = "'.$dados['nm_status_pedido'].'"';
            $query = $this->db->query($sql);
            $res = ObjectToArray($query->getResult())[0];

            $cd_pedido = $dados['cd_pedido'];
            $cd_status_pedido = $res['cd_status_pedido'];
            return $this->db->query("CALL AtualizarStatusPedido('{$cd_status_pedido}',{$cd_pedido})");
        }

        public function getStatusPedido($status) {
            $STATUS_PEDIDO = [
                "APR" => "APROVADO", // cliente
                "CAN" => "CANCELADO", // cliente
                "REC" => "RECUSADO", // fornecedor
                "ACE" => 'ACEITO PELO FORNECEDOR', // fornecedor
                'ANA'=> 'EM ANÁLISE', // cliente
                'POSSE_CLI'=> 'EM POSSE DO CLIENTE', // fornecedor
                'POSSE_FORN'=> 'EM POSSE DO FORNECEDOR', // fornecedor
                'ROTA_ENTREGA'=> 'EM ROTA DE ENTREGA', // fornecedor
                "ASSINADO" => "DOCUMENTO ASSINADO", // docusign
                'AGUA_ASS' => 'AGUARDANDO ASSINATURAS',
                'AGU_PGTO' => 'AGUARDANDO PAGAMENTO'
            ];

            return $STATUS_PEDIDO[$status];
        }

        public function setEnvelopeId($cd_pedido, $envelopeId) {
            $sql = 'UPDATE tb_pedido SET id_contrato_docusign = "'.$envelopeId.'" WHERE cd_pedido = '. $cd_pedido;
            $this->db->query($sql);
        }

        public function pedidoAssinado($envelopeId) {
            $sql = "SELECT * FROM tb_pedido WHERE id_contrato_docusign = '".$envelopeId."'";
            $query = $this->db->query($sql);
            $pedido = ObjectToArray($query->getResult())[0];

            $sql = 'UPDATE tb_pedido SET cd_status_pedido = "11" WHERE cd_pedido = '. $pedido['cd_pedido'];
            $this->db->query($sql);
        }
	}