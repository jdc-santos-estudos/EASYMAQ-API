<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Usuario_model;

use \Firebase\JWT\JWT;

class Maquina extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function cadastrar()
  {
    try {
      if(!$this->autenticarUsuario(['FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($perfilData,'máquina cadastrada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar a máquina');
    }
  }

  public function atualizar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($perfilData,'máquina atualizada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar a máquina');
    }
  }

  public function listar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }
        
      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
   
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess($perfilData,'máquinas listadas com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar as máquinas');
    }
  }

  public function deletar($cd_maquina)
  {
    try {
      $logado = !$this->autenticarUsuario(['ADMIN1', 'ADMIN2','FORNECEDOR']);
        
      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
   
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess($perfilData,'máquina deletada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar deletar a máquina');
    }
  }
}