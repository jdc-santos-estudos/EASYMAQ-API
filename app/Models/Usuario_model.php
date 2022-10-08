<?php 
	namespace App\Models;

	use App\Models\EM_model;

	class Usuario_model extends EM_model {
    
    public function __construct() {
      parent::__construct();
      $this->builder = $this->db->table('tb_usuario');
    }
		
    public function logar($email, $pw) {

      $email = addslashes($email);
      $pw  = addslashes($pw);

      // monta a consulta
      $sql = "SELECT
                cd_usuario, nm_usuario, tb_perfil.cd_perfil cd_perfil, ds_senha, cd_tipo
              FROM 
                tb_usuario 
              JOIN 
                tb_perfil
              ON  
                tb_perfil.cd_perfil = tb_usuario.cd_perfil
              WHERE ds_email = '".$email."'";

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
        'nm_usuario' => $dados->nm_usuario,
        'ds_email' => $dados->ds_email,
        'ds_senha' => password_hash($dados->ds_senha.'.'. getenv('JWT_SECRET'), PASSWORD_BCRYPT),
        'cd_perfil' => $dados->cd_perfil,
        'cd_cidade' => $dados->cd_cidade,
        'dt_criacao' => date('Y-m-d H:i:s')
      ];

      $data['status_usuario'] = 'INATIVO';

      return $this->builder->insert($data);
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

    public function deleteAccount($cd) {
      $sql = 'UPDATE tb_usuario SET ds_email = "#DELETADO#" + ds_email, status_usuario = "INATIVO" WHERE cd_usuario = ?';

      // executa a consulta
      $query = $this->db->query($sql,[$cd]);

      // recupera os dados da consulta como Array
      $res = ObjectToArray($query->getResult());
      
      // se encontrou alguem usuário com este email, retorna os dados dele.
      if ($res) return $res[0];
    }
	}