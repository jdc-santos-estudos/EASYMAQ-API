<?php

namespace App\Controllers;

use App\Controllers\API;

use App\Models\Categoria_model;

class Categoria extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function cadastrar()
  {
    try {  
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2'])) return $this->HttpError400([], 'token de acesso inválido');

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'nm_categoria' => 'required|regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]' // deve conter apenas letras (com e sem acentos) e espaco
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // resto do codigo >>>>>>>>>>>>>>>>>>>>>

      // resto do codigo <<<<<<<<<<<<<<<<<<<<<
      
      return $this->HttpSuccess([],'categoria cadastrada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar categoria.');
    }
  }

  public function atualizar()
  {
    try {  
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2'])) return $this->HttpError400([], 'token de acesso inválido');

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'cd_categoria'    => 'numeric', // deve ser numerico
        'nm_categoria' => 'regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]' // deve conter apenas letras (com e sem acentos) e espaco
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // resto do codigo >>>>>>>>>>>>>>>>>>>>>

      // resto do codigo <<<<<<<<<<<<<<<<<<<<<
      
      return $this->HttpSuccess([],'categoria atualizada com sucesso');
    } catch(\Exception $e) {
      // retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar a categoria.');
    }
  }

  public function listar()
  {
    try {
      
      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'cd_categoria' => 'numeric', // deve ser numerico
        'nm_categoria' => 'regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]' // deve conter apenas letras (com e sem acentos) e espaco
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // resto do codigo >>>>>>>>>>>>>>>>>>>>>

      // resto do codigo <<<<<<<<<<<<<<<<<<<<<
      
      return $this->HttpSuccess([],'categoria atualizada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar a categoria.');
    }
  }

  public function excluir($cd) {
    try {
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2'])) return $this->HttpError400([], 'token de acesso inválido');

      $this->validation->setRules([
        'cd_categoria' => 'required|numeric', // é obrigatório e deve ser numerico
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->run(['cd_categoria' => $cd])) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }
      
      // resto do codigo >>>>>>>>>>>>>>>>>>>>>

      // resto do codigo <<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess([], 'categoria deletada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar deletar a categoria.');
    }
  }
}