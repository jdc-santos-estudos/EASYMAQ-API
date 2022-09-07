<?php 
	namespace App\Models;

	use CodeIgniter\Model;

	class Usuario_model extends Model {
		
    public function logar($email, $pw) {

      $email = addslashes($email);
      $pw  = addslashes($pw);

      // monta a consulta
      $sql = "SELECT cd_usuario, nm_usuario, cd_perfil, ds_senha FROM tb_usuario WHERE ds_email = '".$email."'";

      // executa a consulta
      $query = $this->db->query($sql);

      // recupera os dados da consulta como Array
      $res = ObjectToArray($query->getResult());
      
      // se encontrou alguem usuário com este email...
      if (count($res)) {
    
        // se a senha enviada for compativel com a que está no BD...
        if (password_verify($pw.'.'. getenv('JWT_SECRET'),$res[0]['ds_senha'])) {
          
          // tira os dados do usuário do array.
          $user = $res[0];

          // remove a senha do array do usuário para não mostra-lá no front.
          unset($user['ds_senha']);

          // retorna os dados do usuário.
          return $user;
        }
      }
    }

    public function cadastrar($dados) {
      $data = [
        'nm_usuario' => $dados->nome,
        'ds_email' => $dados->email,
        'ds_senha' => $dados->senha,
        'cd_perfil' => $dados->perfil,
        'cd_cidade' => $dados->cidade,
        'status_usuario' => $dados->status
      ];

      $dataSql = [];

      // adiciona barra inverida antes dos caracteres especiais \', ou \# etc...
      foreach ($data as $campo => $valor) {
        $dataSql[] = addslashes($valor);
      }

      // monta a query
      $sql = 'INSERT INTO tb_usuario (nm_usuario, ds_email, ds_senha, cd_perfil, cd_cidade, status_usuario) VALUES (?,?,?,?,?,?)';

      // se cadastrou o usuário com sucesso, retorna true;
      if ($this->db->query($sql,$dataSql)) return true;
    }

    public function buscarPorEmail($email) {
      $email = addslashes($email);

      // monta a consulta
      $sql = 'SELECT cd_usuario, nm_usuario, cd_perfil FROM tb_usuario WHERE ds_email = ?';

      // executa a consulta
      $query = $this->db->query($sql,[$email]);

      // recupera os dados da consulta como Array
      $res = ObjectToArray($query->getResult());
      
      // se encontrou alguem usuário com este email, retorna os dados dele.
      if ($res) return $res[0];
    }

    public function getInfo($cd) {

      // monta a consulta
      $sql = 'SELECT 
                u.cd_usuario,
                u.cd_cidade,
                u.cd_perfil,
                u.nm_usuario,
                u.ds_email,
                u.nm_razao_social,
                u.nm_fantasia,
                p.cd_tipo tipo_perfil
              FROM 
                tb_usuario as u
              JOIN
                tb_perfil as p
              ON 
                u.cd_perfil = p.cd_perfil
              WHERE 
                u.cd_usuario = ?';

      // executa a consulta
      $query = $this->db->query($sql,[$cd]);

      // recupera os dados da consulta como Array
      $res = ObjectToArray($query->getResult());
      
      // se encontrou alguem usuário com este email, retorna os dados dele.
      if ($res) return $res[0];
    }
	}