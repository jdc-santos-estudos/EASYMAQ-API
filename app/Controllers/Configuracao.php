<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Configuracao_model;

class Configuracao extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function getConfigFront()
  {
    try {
      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([ 'version'    => 'required' ]);

      // // executando a validação dos erros
      $this->validation->withRequest($this->request)->run();

      // // recuperando os erros da validação
      $errors = $this->validation->getErrors();

      // // verificando se existe erro nos campos, se existir, retorna a mensagem de erro.
      if ($errors) { return $this->HttpError400($errors, 'campos inválidos'); }

      // instanciando um objeto da classe Usuario_model
      $config = new Configuracao_model();

      $version = $this->request->getGet('version');

      // chamando a função de logar do usuário
      $configFront = $config->getConfigFront($version);

      // verifica se encontrou algum usuário na busca, se nao encontrou, retorna a mensagem de erro.
      // if (!$userData) { return $this->HttpError400([], 'email ou senha incorretos.'); }
      
      // // gerando o token de autenticação
      // $token = JWT_generate($userData);

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($configFront,'configurações recuperadas com sucesso');

    } catch(\Exception $e) {

      // retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro recuperar as configurações do front');

    }
  }
}