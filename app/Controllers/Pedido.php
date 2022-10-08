<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Usuario_model;

use \Firebase\JWT\JWT;

class Pedido extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function novo()
  {
    try {
      if(!$this->autenticarUsuario(['CLIENTE'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($perfilData,'pedido efetuado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar efetuar o pedido');
    }
  }

  public function atualizar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      // deve verificar o perfil do usuário e verificar quem pode atualizar o pedido para qual perfil:
      // exemplo: 
      // apenas o FORNECEDOR pode atualizar o pedido para ACEITO PELO FORNECEDOR
      // apenas o CLIENTE pode atualizar o pedido para PEDIDO ENTREGUE
      // etc...
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($perfilData,'pedido atualizado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar o pedido');
    }
  }

  public function listar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      // deve verificar o perfil do usuário e verificar quem pode atualizar o pedido para qual perfil:
      // exemplo: 
      // apenas o FORNECEDOR e o CLIENTE poderão listar apenas os pedidos que pertencerem a eles.
      // os admins poderão listar todos os pedidos
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess($perfilData,'pedidos listados com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar os pedidos');
    }
  }

  public function baixarContrato($cd_contrato)
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess($perfilData,'contrato recuperado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar recuperar o contrato');
    }
  }
}