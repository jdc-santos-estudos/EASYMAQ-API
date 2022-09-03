<?php

namespace App\Controllers;

use App\Controllers\API;

use App\Models\Usuario_model;

class Home extends API
{
    public function index()
    {
      try {
        return $this->HttpSuccess([], 'mensagem de sucesso!');
      } catch(\Exception $e) {
        return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno...');
      }
    }
}