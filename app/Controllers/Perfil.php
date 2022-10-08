<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Perfil_model;

use \Firebase\JWT\JWT;

class Perfil extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function listar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // instanciando um objeto da classe Usuario_model
      $perfil = new Perfil_model();
      // chamando a função de logar do usuário
      $perfilData = $perfil->listar();

      // verifica se encontrou algum usuário na busca, se nao encontrou, retorna a mensagem de erro.
      if (!$perfilData) { return $this->HttpError400([], 'erro ao listar os perfis.'); }
      
      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($perfilData,'perfis listados com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar os perfis');
    }
  }
}