<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Usuario_model;

use \Firebase\JWT\JWT;

class Dashboard extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function login()
  {
    try {

      // instanciando um objeto da classe Usuario_model
      $user = new Usuario_model();

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'email'    => 'required|valid_email',
        'senha' => 'required|min_length[6]'
      ]);

      // executando a validação dos erros
      $this->validation->withRequest($this->request)->run();

      // recuperando os erros da validação
      $errors = $this->validation->getErrors();

      // verificando se existe erro nos campos, se existir, retorna a mensagem de erro.
      if ($errors) { return $this->HttpError400($errors, 'campos inválidos'); }

      // pegando os dados do request de login.
      $email = $this->request->getVar('email');
      $senha = $this->request->getVar('senha');

      // chamando a função de logar do usuário
      $userData = $user->logar($email, $senha);

      // verifica se encontrou algum usuário na busca, se nao encontrou, retorna a mensagem de erro.
      if (!$userData) { return $this->HttpError400([], 'email ou senha incorretos.'); }
      
      // gerando o token de autenticação
      $token = JWT_generate($userData);

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess(["token" => $token],'login efetuado com sucesso');

    } catch(\Exception $e) {

      // retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro ao efetuar o login');

    }
  }
}