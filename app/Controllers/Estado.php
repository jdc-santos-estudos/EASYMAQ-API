<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Estado_model;

class Estado extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function listar()
  {
    try {
      $estado = new Estado_model();
      
      $estados = $estado->listar();

      if (!$estados) { return $this->HttpError400([], 'erro ao listar os estados.'); }
      
      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($estados,'estados listados com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar os estados');
    }
  }
}