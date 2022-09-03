<?php

namespace App\Controllers;

use App\Controllers\API;

use App\Models\Usuario_model;

class Usuario extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function cadastrar()
  {
    try {
  
      // instanciando um objeto da classe Usuario_model
      $user = new Usuario_model();

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'nome' => 'required|min_length[3]',
        'email'    => 'required|valid_email',
        'senha' => 'required|min_length[6]',
        'conf_senha' => 'required|matches[senha]',
      ]);

      // executando a validação dos erros
      $this->validation->withRequest($this->request)->run();

      // recuperando os erros da validação
      $errors = $this->validation->getErrors();

      // verificando se existe erro nos campos, se existir, retorna a mensagem de erro.
      if ($errors) { return $this->HttpError400($errors, 'Falha ao tentar cadastrar usuário'); }

      // recupera o email do request.
      $email = $this->request->getVar('email');

      // procura um usuário pelo email enviado.
      $userData = $user->buscarPorEmail($email);
      
      // se encontrou um usuário com o mesmo email, retorna a mensagem
      if($userData) return $this->HttpError400([], 'email já cadastrado');

      // chama função de cadastro de usuário
      $userData = $user->cadastrar($this->request->getVar());

      // retorna a mensagem de sucesso
      return $this->HttpSuccess([],'usuário cadastrado com sucesso');

    } catch(\Exception $e) {
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar usuário.');
    }
  }

  public function perfil() {
    try {

      // recupera o token do header da requisição.
      $token = $this->request->getServer('HTTP_AUTHORIZATION');
      
      // valida o token, se estiver tudo OK, retorna os dados.
      $xdecoded = JWT_validate($token);

      // se o token for inválido ou estiver expirado, retorna o erro.
      if (!$xdecoded) return $this->HttpError400([], 'Token inválido');
    
      return $this->HttpSuccess($xdecoded['data'], 'dados da conta recuperados com sucesso');

    } catch(\Exception $e) {
      
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar usuário.');

    }
  }
}