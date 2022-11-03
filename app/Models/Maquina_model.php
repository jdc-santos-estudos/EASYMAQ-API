<?php 
	namespace App\Models;

	use CodeIgniter\Model;

	class Maquina_model extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->builder = $this->db->table('tb_maquina');
    }
		
    public function cadastrar($dados) {        
        if ($this->builder->insert($dados)) return $this->db->insertID();
    }

    public function atualizar($cd_maquina, $dados) {
        
        $keys = array_keys($dados);
        $values = array_values($dados);

        for ($i =0 ; $i < count($dados); $i++) {
            $this->builder->set($keys[$i], $values[$i]);
        }
        
        $this->builder->where('cd_maquina', $cd_maquina);
        return $this->builder->update();
    }

    public function listarDisponiveis($filtros) {
        $sql = 'SELECT 
                    m.*,
                    u.nm_usuario as nm_fornecedor,
                    u.nm_razao_social as nm_razao_social_fornecedor,
                    u.nm_fantasia as nm_fantasia_fornecedor,
                    c.nm_cidade,
                    e.cd_estado, e.nm_estado, e.ds_sigla,
                    cat.nm_categoria
                FROM tb_maquina m
                JOIN tb_cidade c ON c.cd_cidade = m.cd_cidade
                JOIN tb_estado e ON e.cd_estado = c.cd_estado
                JOIN tb_categoria cat ON cat.cd_categoria = m.cd_categoria
                JOIN tb_usuario u ON u.cd_usuario = m.cd_fornecedor
                WHERE m.cd_status ="ATIVO" ';

        if (count($filtros)) {
            if (isset($filtros['cd_usuario']) && is_numeric($filtros['cd_usuario'])) {
                $sql .= " AND m.cd_fornecedor = ". $filtros['cd_usuario'];
            }

            if (isset($filtros['cd_cidade']) && is_numeric($filtros['cd_cidade'])) {
                $sql .= " AND m.cd_cidade = ". $filtros['cd_cidade'];
            }

            if (isset($filtros['cd_estado']) && is_numeric($filtros['cd_estado'])) {
                $sql .= " AND e.cd_estado = ". $filtros['cd_estado'];
            }

            if (isset($filtros['cd_categoria']) && is_numeric($filtros['cd_categoria'])) {
                $sql .= " AND m.cd_categoria = ". $filtros['cd_categoria'];
            }

            if (isset($filtros['cd_maquina']) && is_numeric($filtros['cd_maquina'])) {
                $sql .= " AND m.cd_maquina = ". $filtros['cd_maquina'];
            }

            if (isset($filtros['vl_min']) && is_numeric($filtros['vl_min'])) {
                $sql .= " AND m.vl_hora >= ". $filtros['vl_min'];
            }

            if (isset($filtros['vl_max']) && is_numeric($filtros['vl_max'])) {
                $sql .= " AND m.vl_hora <= ". $filtros['vl_max'];
            }

            if (isset($filtros['search']) && strlen($filtros['search']) > 0) {
                $sql .= ' AND (
                    u.nm_usuario LIKE "%'.addslashes($filtros['search']).'%" OR
                    u.nm_razao_social LIKE "%'.addslashes($filtros['search']).'%" OR
                    cat.nm_categoria LIKE "%'.addslashes($filtros['search']).'%" )';
            }
        }

        // executa a consulta
        $query = $this->db->query($sql);

        // recupera os dados da consulta como Array
        return ObjectToArray($query->getResult());
    }

    public function listarPorId($ids) {

        $sql = 'SELECT 
                    m.*,
                    u.nm_usuario as nm_fornecedor, u.ds_email as ds_email_fornecedor,
                    c.nm_cidade,
                    e.cd_estado, e.nm_estado, e.ds_sigla,
                    cat.nm_categoria
                FROM tb_maquina m
                JOIN tb_cidade c ON c.cd_cidade = m.cd_cidade
                JOIN tb_estado e ON e.cd_estado = c.cd_estado
                JOIN tb_categoria cat ON cat.cd_categoria = m.cd_categoria
                JOIN tb_usuario u ON u.cd_usuario = m.cd_fornecedor
                WHERE m.cd_status ="ATIVO" AND  m.cd_maquina IN(';

        if (is_array($ids)) {
            for ($i = 0; $i < count($ids); $i++) { $sql .=  $ids[$i]['cd_maquina'].','; }
            $sql = rtrim($sql,',');
        } else if (is_numeric($ids)) {
            $sql .= $ids;
        }

        $sql.= ')';
        
        // executa a consulta
        $query = $this->db->query($sql);

        // recupera os dados da consulta como Array
        return ObjectToArray($query->getResult());
    }

    public function listarPorPedido($pedidosIds) {
        $sql = 'SELECT 
                    m.*,
                    c.nm_cidade,
                    e.cd_estado, e.nm_estado, e.ds_sigla,
                    cat.nm_categoria,
                    pm.vl_hora as vl_hora_contratada, pm.cd_pedido,
                    f.nm_usuario as nm_fornecedor, f.nm_razao_social as nm_razao_social_fornecedor, f.nm_fantasia as nm_fantasia_fornecedor,
                    cli.nm_usuario as nm_cliente, cli.nm_razao_social as nm_razao_social_cliente, cli.nm_fantasia as nm_fantasia_cliente
                FROM tb_maquina m
                JOIN tb_cidade c ON c.cd_cidade = m.cd_cidade
                JOIN tb_estado e ON e.cd_estado = c.cd_estado
                JOIN tb_categoria cat ON cat.cd_categoria = m.cd_categoria
                JOIN tb_usuario f ON f.cd_usuario = m.cd_fornecedor
                JOIN tb_pedido_maquina pm ON pm.cd_maquina = m.cd_maquina
                JOIN tb_pedido p ON p.cd_pedido = pm.cd_pedido
                JOIN tb_usuario cli ON cli.cd_usuario = p.cd_usuario
                WHERE m.cd_maquina > 0 AND  pm.cd_pedido IN(';


        $sql .= implode(',', $pedidosIds). ')';
        
        // executa a consulta
        $query = $this->db->query($sql);

        // recupera os dados da consulta como Array
        return ObjectToArray($query->getResult());
    }

    public function listarPorPerfil($filtros) {
        $sqlFields = 'SELECT 
                        m.*,
                        u.nm_usuario as nm_fornecedor,
                        c.cd_cidade, c.nm_cidade,
                        e.cd_estado, e.nm_estado,
                        cat.cd_categoria, cat.nm_categoria';

        $sqlTables = '
                FROM tb_maquina m
                JOIN tb_cidade c ON c.cd_cidade = m.cd_cidade
                JOIN tb_estado e ON e.cd_estado = c.cd_estado
                JOIN tb_categoria cat ON cat.cd_categoria = m.cd_categoria
                JOIN tb_usuario u ON u.cd_usuario = m.cd_fornecedor ';

        $sqlWHERE = 'WHERE m.cd_maquina > 0 ';

        if (count($filtros)) {
            
            if ($filtros['cd_cliente']) {
                $sqlFields .= ', cli.nm_usuario as nm_cliente ';
                $sqlTables .= ' 
                    JOIN tb_pedido_maquina pmx ON pmx.cd_maquina = m.cd_maquina
                    JOIN tb_pedido p ON p.cd_pedido = pm.cd_pedido
                    JOIN tb_usuario cli ON cli.cd_usuario = p.cd_usuario';
                $sqlWHERE .= ' AND cli.cd_usuario = '.$filtros['cd_cliente'].' ';
            }

            if (isset($filtros['cd_maquina'])) $sqlWHERE .= ' AND m.cd_maquina = '. $filtros['cd_maquina'];
        }
        $sql = $sqlFields.$sqlTables.$sqlWHERE;
        $sql .= ' ORDER BY cd_maquina';

      // executa a consulta
      $query = $this->db->query($sql);

      // recupera os dados da consulta como Array
      return ObjectToArray($query->getResult());
    }

    public function excluir($cd_maquina) {
        // recuperando as imagens da máquina
        $builder = $this->db->table('tbl_maquina_imagem');
        $query = $builder->getWhere(['cd_maquina' => $cd_maquina]);
        $imagens = ObjectToArray($query->getResult());

        // deletando as imagens do folder
        for ($i =0; $i < count($imagens); $i++) {

            $pathImg  = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;
            $pathImg .= getenv('usar_public_dir') == '1' ? 'public'.DIRECTORY_SEPARATOR: '';
            $pathImg .="imagens".DIRECTORY_SEPARATOR;
            unlink($pathImg.$imagens[$i]['nm_imagem']);
        }

        // deletando as imagens da máquina do DB
        $builder->where('cd_maquina', $cd_maquina);
        $builder->delete();

        // deletando a máquina
        $this->builder->where('cd_maquina', $cd_maquina);
        return $this->builder->delete();
    }

    public function getImagens($maquina) {
        
        $maquinasImgs = [];
        
        if (is_numeric($maquina)) {
            $builder = $this->db->table('tbl_maquina_imagem');
            $query = $builder->getWhere(['cd_maquina' => $maquina]);
            $maquinasImgs = ObjectToArray($query->getResult());
        }

        if(is_array($maquina)) {
            $sql = 'SELECT * FROM tbl_maquina_imagem WHERE cd_maquina in(';
            for ($i = 0; $i < count($maquina); $i++) { $sql .=  $maquina[$i]['cd_maquina'].','; }
            
            $sql = rtrim($sql,',') .')';
    
            $query = $this->db->query($sql);
            $maquinasImgs = ObjectToArray($query->getResult());
        }
        
        for ($i=0; $i < count($maquinasImgs); $i++) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            $maquinasImgs[$i]['fileUrl'] = $protocol.'://'.$_SERVER['HTTP_HOST'].'/imagens/'.$maquinasImgs[$i]['nm_imagem'];
        }

        return $maquinasImgs;
    }

    public function deletarImagens($imagens) {
        if (count($imagens)) {

            // recuperando as imagens da máquina
            $builder = $this->db->table('tbl_maquina_imagem');
            $builder->whereIn('idtbl_maquina_imagem', $imagens);
            $query = $builder->get();
            $dbImagens = ObjectToArray($query->getResult());            

            // deletando as imagens do folder
            for ($i =0; $i < count($dbImagens); $i++) {
                $pathImg = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;
                $pathImg .= getenv('usar_public_dir') == '1' ? 'public'.DIRECTORY_SEPARATOR: '';
                $pathImg .="imagens".DIRECTORY_SEPARATOR;
                unlink($pathImg.$dbImagens[$i]['nm_imagem']);
            }
            
            for ($i = 0; $i < count($imagens); $i++) {
                $sql = 'DELETE FROM tbl_maquina_imagem WHERE idtbl_maquina_imagem = '. $imagens[$i];
                $this->db->query($sql);
            }
        }
    }

    public function cadastrarImagens($imagens) {
        $builder = $this->db->table('tbl_maquina_imagem');
        $builder->insertBatch($imagens);
    }
}