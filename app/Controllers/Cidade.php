<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Cidade_model;

class Cidade extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function listar($cd_estado)
  {
    try {
      $cidade = new Cidade_model();
      
      $cidades = $cidade->listar($cd_estado);

      if (!$cidades) { return $this->HttpError400([], 'erro ao listar as cidades.'); }
      
      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($cidades,'cidades listadas com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar as cidades');
    }
  }
}