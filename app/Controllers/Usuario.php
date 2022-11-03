<?php

namespace App\Controllers;

use App\Controllers\API;

use App\Models\Usuario_model;

use \Firebase\JWT\JWT;

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
        'nm_usuario' => 'required|min_length[3]',
        'ds_email'    => 'required|valid_email',
        'ds_senha' => 'required|min_length[6]',
        'conf_senha' => 'required|matches[ds_senha]',
        'cd_perfil' => 'required|in_list[3,4]', // cliente ou fornecedor
        'cd_cep' => 'required',
        'cd_cidade' => 'required',
        'nm_logradouro' => 'required',
        'nr_local' => 'required',
        'nm_bairro' => 'required',
        'ds_telefone' => 'required'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // procura um usuário pelo email enviado.
      $userData = $user->buscarPorEmail($this->request->getVar('ds_email'));
      
      // se encontrou um usuário com o mesmo email, retorna a mensagem
      if($userData) return $this->HttpError400([], 'email já cadastrado');

      // chama função de cadastro de usuário
      $dados = json_decode(json_encode($this->request->getVar()),1);
      
      unset($dados['conf_senha']);
      unset($dados['cd_estado']);

      $userId = $user->cadastrar($dados);

      if(!is_numeric($userId)) return $this->HttpError400([], 'Erro tentar cadastrar o usuário');

      // dispara o evento que envia o email
      $jwtConfirm = JWT_generate(['id' => $userId]);

      $payload = [
        'email' => $dados['ds_email'],
        'template' => 'cadastro',
        'replace' => [
          'token' => $jwtConfirm
        ]
      ];

      $msg = JWT_generate($payload);    
      $c = base64_encode($this->encrypter->encrypt($msg, getenv('api_email_key')));

      try {
        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', getenv('API_EMAIL').'enviar-email',['json' => ['data' => $c]]);

        $resEmail = json_decode($response->getBody(),1);

        return $this->HttpSuccess([],'usuário cadastrado com sucesso');
      } catch(\Exception $e) {
        return $this->HttpError500([], $e, json_encode($payload), 'Erro interno ao tentar enviar email para o usuário.');
      }

    } catch(\Exception $e) {
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar usuário.');
    }
  }

  public function cadastrarAdmin()
  {
    try {

      if(!$this->autenticarUsuario(['ADMIN1'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }
  
      // instanciando um objeto da classe Usuario_model
      $user = new Usuario_model();

      // definindo validações que os campos precisarão passar.
      $this->validation->setRules([
        'nm_usuario'  => 'required|min_length[3]',
        'ds_email' => 'required|valid_email',
        'cd_cidade' => 'required|numeric'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // procura um usuário pelo email enviado.
      $userData = $user->buscarPorEmail($this->request->getVar('ds_email'));
      
      // se encontrou um usuário com o mesmo email, retorna a mensagem
      if($userData) return $this->HttpError400([], 'email já cadastrado');

      $dados = json_decode(json_encode($this->request->getVar()),1);
      $dados['cd_perfil'] = 2;

      function generatePw($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
    
        for ($i = 0; $i < $n; $i++) {
          $index = rand(0, strlen($characters) - 1);
          $randomString .= $characters[$index];
        }
    
        return $randomString;
      }
      
      $dados['ds_senha'] = generatePw(12);

      // chama função de cadastro de usuário
      if(!$user->cadastrar($dados)) return $this->HttpError400([], 'Erro tentar cadastrar o usuário');
      
      $payload = [
        'email' => $dados['ds_email'],
        'template' => 'cadastroAdmin',
        'replace' => [
          'ds_senha' => $dados['ds_senha']
        ]
      ];

      $msg = JWT_generate($payload);
    
      $c = base64_encode($this->encrypter->encrypt($msg, getenv('api_email_key')));

      try {
        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', getenv('API_EMAIL').'enviar-email',['json' => ['data' => $c]]);

        $resEmail = json_decode($response->getBody(),1);

        return $this->HttpSuccess([],'usuário cadastrado com sucesso');
      } catch(\Exception $e) {
        return $this->HttpError500([], $e, json_encode($payload), 'Erro interno ao tentar enviar email para o usuário.');
      }

    } catch(\Exception $e) {
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar usuário.');
    }
  }

  public function perfil() {
    try {
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido');
      }
         
      return $this->HttpSuccess($this->userData, 'dados da conta recuperados com sucesso');

    } catch(\Exception $e) {
      
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar usuário.');

    }
  }

  public function getInfo() {
    try {
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido');
      }
        
      $user = new Usuario_model();

      $userInfo = $user->getInfo($this->userData->cd_usuario);

      return $this->HttpSuccess($userInfo, 'dados da conta recuperados com sucesso');

    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar recuperar os dados usuário logado.');
    }
  }

  public function deletarConta() {
    try {
      if(!$this->autenticarUsuario(['ADMIN1','ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido');
      }

      $this->validation->setRules([
        'confirma_deletar'  => 'required'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // confirmando se o campo de confirmacao de exclusão da conta foi preenchido
      if (!$this->request->getVar('confirma_deletar')) {
        return $this->HttpError400([], 'o campo de confirmação de exclusão da conta deve ser selecionado');
      }
      // ---------------------------------------------------------
      // se o usuário for cliente, deve verificar se nao possui nenhuma máquina alugada
      // se o usuário for fornecedor, deve verificar se nao possui nenhuma máquina alocada para algum cliente
      // caso tenham, deve impedir que a conta seja deletada, a conta só poderá ser deletaca caso nao tenham máquinas vinculadas a contratos em vigência.
      // ---------------------------------------------------------

      $user = new Usuario_model();

      // deletando o usuário
      $user->deleteAccount($this->userData->cd_usuario);

      return $this->HttpSuccess([], 'conta deletada com sucesso');

    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar deletar a conta.');
    }
  }

  public function listarFornecedores() {
    try {        
      $user = new Usuario_model();

      $fornecedores = $user->listarFornecedores();

      return $this->HttpSuccess($fornecedores, 'fornecedores listados com sucesso');

    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar os fornecedores.');
    }
  }

  public function confirmEmail() {
    try {        
      $userData = (JWT::decode($this->request->getVar('token'), getenv('JWT_SECRET'), array("HS256")))->data;
      $user = new Usuario_model();
      
      $tokenLogin = $user->ativarConta($userData->id);
      if(!is_array($tokenLogin) && !is_bool($tokenLogin)) return $this->HttpError400([], 'Token inválido ou já utilizado');

      $token = false;

      if (is_array($tokenLogin)) $token = JWT_generate($tokenLogin);
      
      return $this->HttpSuccess($token,'email autenticado efetuado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar os fornecedores.');
    }
  }
}