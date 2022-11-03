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

  public function getConfigs() 
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }
      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'config' => 'permit_empty|regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ_]|\s|)+$/]'
      ]);

      // // executando a validação dos erros
      $this->validation->withRequest($this->request)->run();

      // // recuperando os erros da validação
      $errors = $this->validation->getErrors();

      // // verificando se existe erro nos campos, se existir, retorna a mensagem de erro.
      if ($errors) { return $this->HttpError400($errors, 'campos inválidos'); }

      // instanciando um objeto da classe Usuario_model
      $config = new Configuracao_model();
      $dados = json_decode(json_encode($this->request->getVar()),1);

      // chamando a função de logar do usuário
      $config = $config->getConfig($dados);

      if (count($config) === 1) {
        $config[0]['ds_valor'] = stripslashes($config[0]['ds_valor']);
        $config[0]['ds_valor'] = json_decode($config[0]['ds_valor'],1);
      }

      // verifica se encontrou algum usuário na busca, se nao encontrou, retorna a mensagem de erro.
      // if (!$userData) { return $this->HttpError400([], 'email ou senha incorretos.'); }
      
      // // gerando o token de autenticação
      // $token = JWT_generate($userData);

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($config,'configurações recuperadas com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro recuperar as configurações do front');
    }
  }

  public function getTermos()
  {
    try {
      // instanciando um objeto da classe Usuario_model
      $config = new Configuracao_model();

      $dados['config'] = 'TERMOS_USO';
          
      $config = $config->getConfig($dados);

      if (count($config) === 1) {
        $config[0]['ds_valor'] = stripslashes($config[0]['ds_valor']);
        $config[0]['ds_valor'] = json_decode($config[0]['ds_valor'],1);
      }
    
      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($config[0]['ds_valor'],'termos de uso recuperados com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro recuperar os termos de uso');
    }
  }
  
  public function getConfigFront()
  {
    try {
      // instanciando um objeto da classe Usuario_model
      $config = new Configuracao_model();

      $dados['config'] = 'CONFIG_FRONT';
          
      $config = $config->getConfig($dados);

      if (count($config) === 1) {
        $config[0]['ds_valor'] = stripslashes($config[0]['ds_valor']);
        $config[0]['ds_valor'] = json_decode($config[0]['ds_valor'],1);
      }
    
      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($config[0]['ds_valor'],'configurações recuperadas com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro recuperar as configurações do front');
    }
  }

  public function saveConfig() {
    try {
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'config' => 'regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ_]|\s|)+$/]',
      ]);

      // // executando a validação dos erros
      $this->validation->withRequest($this->request)->run();

      // // recuperando os erros da validação
      $errors = $this->validation->getErrors();

      // // verificando se existe erro nos campos, se existir, retorna a mensagem de erro.
      if ($errors) { return $this->HttpError400($errors, 'campos inválidos'); }

      $dados = json_decode(json_encode($this->request->getVar()),1);

      // instanciando um objeto da classe Usuario_model
      $config = new Configuracao_model();
      $config->salvar($dados);

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess([],'configurações atualizadas com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro salvar as configurações');
    }
  }
}