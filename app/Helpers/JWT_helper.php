<?php 
  use \Firebase\JWT\JWT;

  if (!function_exists('JWT_generate')) {
    function JWT_generate($userData) {

      // pega a data atual (em segundos)
      $hora = 3600;
      $iat = time(); // pegando a data em segundos
      $nbf = $iat; // o momento em que o token vai começar a "valer" (atual)
      $exp = $iat + $hora * 24 * 7; // o momento em que o token vai expirar, expira em 7 dias

      $payload = array(
        "iss" => "EasyMAQ_API",
        "aud" => "EasyMAQ_FRONT",
        "iat" => $iat,
        "nbf" => $nbf,
        "exp" => $exp,
        "data" => $userData
      );

      JWT::$leeway = 60;
      return JWT::encode($payload,getenv('JWT_SECRET'), "HS256");
    }
  }

  if (!function_exists('JWT_validate')) {
    function JWT_validate() {
      try {
        return (JWT::decode($_SERVER['HTTP_AUTHORIZATION'], getenv('JWT_SECRET'), array("HS256")))->data;
      } catch(\Exception $e) {
        return false;
      }
    }
  }