<?php

namespace App\Controllers;

use \Firebase\JWT\JWT;

class Dashboard extends BaseController
{
  public function login()
  {
    try {

      $iat = time();
      $nbf = $iat + 10;
      $exp = $iat + 3600;
     
      $payload = array(
          "iss" => "The_claim",
          "aud" => "The_Aud",
          "iat" => $iat,
          "nbf" => $nbf,
          "exp" => $exp,
          "data" => [
            "nome" => "Gustavo",
            "perfil" => 'ADMIN'
          ]
      );
     
      $token = JWT::encode($payload, 'segredosecreto', "HS256");

      HttpSuccess([
        "success" => true,
        "data" => [
          "token" => $token
        ]
      ]);

    } catch(Exception $e) {

      HttpError([
        "success" => false,
        "data" => [
          "mensagem" => "Erro interno ao tentar efetuar o login",
          "error" => e->getMessage()
        ]
      ]);
    }
  }
}