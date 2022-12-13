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
      return $this->HttpSuccess($token,'login efetuado com sucesso');

    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro ao efetuar o login');
    }
  }

  public function redefinirSenha() 
  {
    try {

      // instanciando um objeto da classe Usuario_model
      $user = new Usuario_model();

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules(['email'    => 'required|valid_email']);

      // executando a validação dos erros
      $this->validation->withRequest($this->request)->run();

      // recuperando os erros da validação
      $errors = $this->validation->getErrors();

      // verificando se existe erro nos campos, se existir, retorna a mensagem de erro.
      if ($errors) { return $this->HttpError400($errors, 'campos inválidos'); }

      // pegando os dados do request de login.
      $email = $this->request->getVar('email');

      $userData = $user->buscarPorEmail($email);

      try {
  
        $tokenRedefinirSenha = JWT_generate([
          'email' => $email,
        ], [
          "exp" => time() + 600 // expira em 10 minutos
        ]);
    
        $payload = [
          'email' => $email,
          'template' => 'redefinirSenha',
          'replace' => [
            'nm_usuario' => $userData['nm_usuario'],
            'token' => $tokenRedefinirSenha,
          ]
        ];
    
        $msg = JWT_generate($payload);

        $c = base64_encode($this->encrypter->encrypt($msg, getenv('api_email_key')));
    
        $client = \Config\Services::curlrequest();
        $client->request('POST', getenv('api_email').'enviar-email',['json' => ['data' => $c]]);
      } catch (\Exception $e) {
        return $this->HttpError500([], json_encode($dadosEmail),'ERRO AO ENVIAR EMAIL', 'Erro interno ao tentar enviar email para o usuário.');
      }

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($token,'requisicao enviada com sucesso');

    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro ao efetuar a requisicao de redefinicao de senh');
    }
  }
}