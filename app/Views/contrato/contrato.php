<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/css/c.css">
</head>
<body>
<page size="A4">
  <div class="content-page">
    <section>
      <table class="table-logo">
        <tr>
          <td width="100%" height="100" class="logo_axa"></td>
        </tr>
      </table>
    </section>

    <section>      
      <h1 class="bg-white no-padding no-margin">Ficha para Cadastro de Corretor</h1>
    </section>
    <br>
    <section>
      <h2>Informações da Corretora</h2>
      <table>
        <tr>
          <td ng-style="{width: dados.isTemplate ? '40%':'auto'}">
            <p><strong>CPF/CNPJ:</strong> <span>{{ dados.cpf_cnpj }}</span></p>
          </td>
          <td>
            <p><strong>CNPJ da Matriz/Corretora Principal:</strong> <span>{{ dados.cnpj_matriz }}</span></p>
          </td>
        </tr>

        <tr>
          <td>
            <p><strong>Razão Social:</strong> <span>{{ dados.razao_social }}</span></p>
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <p><strong>SUSEP:</strong> <span>{{ dados.susep }}</span></p>
          </td>
        </tr>
      </table>
      <br>
    
      <hr class="blue">
      <h2>Endereço Fiscal</h2>
      <table>
        <tr>
          <td colspan="2">
            <p><strong>Endereço:</strong> <span>{{ dados.endereco.rua }}</span></p>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong>CEP:</strong> <span>{{ dados.endereco.cep }}</span></p>
          </td>
          <td>
            <p><strong>UF:</strong> <span>{{ dados.endereco.estado }}</span></p>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong>Cidade:</strong> <span>{{ dados.endereco.cidade }}</span></p>
          </td>
          <td>
            <p><strong>Bairro:</strong> <span>{{ dados.endereco.bairro }}</span></p>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong>Numero:</strong> <span>{{ dados.endereco.numero }}</span></p>
          </td>
          <td>
            <p><strong>Complemento:</strong> <span>{{ dados.endereco.complemento }}</span></p>
          </td>
        </tr>
      </table>
      <br>

      <hr class="blue">
      <h2>Contato</h2>
      <table>
        <tr>
          <td>
            <p><strong>E-mail Comercial:</strong> <span>{{ dados.contatos.email_comercial }}</span></p>
          </td>
          <td>
            <p><strong>E-mail Cobrança:</strong> <span>{{ dados.contatos.email_cobranca }}</span></p> 
          </td>
        </tr>
        <tr>
          <td>
            <p><strong>E-mail Operacional:</strong> <span>{{ dados.contatos.email_operacional }}</span></p>
          </td>
          <td>
            <p><strong>E-mail Campanhas:</strong> <span>{{ dados.contatos.email_campanhas }}</span></p>
          </td>
        </tr>
        <tr>
          <td>
            <p><strong>E-mail Aviso Sinistro:</strong> <span>{{ dados.contatos.email_aviso_sinistro }}</span></p>
          </td>
          <td>
            <p><strong>Telefone:</strong> <span>{{ dados.contatos.telefone }}</span></p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <p><strong>E-mail Extrato Comissão:</strong> <span>{{ dados.contatos.email_extrato_comissao }}</span></p>              
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <p><strong>E-mail Faturamento/Emissão NF's:</strong> <span>{{ dados.contatos.email_faturamento }}</span></p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <p><strong>E-mail Tratativas Renovação:</strong> <span>{{ dados.contatos.email_tratativas_renovacao }}</span></p>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <p><strong>E-mail Portal e-Solutions:</strong> <span>{{ dados.contatos.email_portal }}</span></p>
          </td>
        </tr>
        <tr>
          
        </tr>
      </table>
      <br>
      <hr class="blue">
      
      <h2>Dados de Pagamento de Comissão e Tributários</h2>
      <br>
      <table>
        <tr>
          <td>
            <strong>Tipo de Conta:</strong>
            <span>{{ dados.pagamento.dados_bancarios.tipo_conta.tipo }}</span>
          </td>
          <td>
            <strong>Banco:</strong>
            <span ng-if="!dados.isTemplate" >{{ dados.pagamento.dados_bancarios.banco.cod +" - "+dados.pagamento.dados_bancarios.banco.nome }}</span>
          </td>
        </tr>
        <tr>
          <td>
            <strong>Agência:</strong>
            <span>{{ dados.pagamento.dados_bancarios.agencia }}</span>
          </td>
          <td>
            <strong>Conta Corrente:</strong>
            <span>{{ dados.pagamento.dados_bancarios.conta_corrente }}</span>
          </td>
        </tr>
      </table>
      <br>
      <hr class="blue">

      <h3>Dados Tributários</h3>
        <table>
          <tr>
            <td>
              <p><strong>Simples Nacional:</strong> <span ng-if="!dados.isTemplate">{{ dados.pagamento.tributos.simples_nacional == 'SIM' ? 'Sim' : 'Não'}}</span></p>
            </td>
            <td>
              <p><strong>Alíquota de ISS:</strong> <span ng-if="dados.pagamento.tributos.iss > 0">{{ dados.pagamento.tributos.iss * 100 + "%" }}</span></p>
            </td>
          </tr>
          <tr>
            <td>
              <p><strong>Tributação de IR:</strong> <span ng-if="dados.pagamento.tributos.ir > 0">{{ dados.pagamento.tributos.ir * 100 + "%" }}</span></p>
            </td>
            <td>
              <p><strong>PIS:</strong> <span ng-if="dados.pagamento.tributos.pis > 0">{{ dados.pagamento.tributos.pis * 100 + "%" }}</span></p>
            </td>
          </tr>
          <tr>
            <td>
              <p><strong>COFINS:</strong> <span ng-if="dados.pagamento.tributos.cofins > 0">{{ dados.pagamento.tributos.cofins * 100 + "%" }}</span></p>
            </td>
            <td>
              <p><strong>INSS:</strong> <span ng-if="dados.pagamento.tributos.inss > 0">{{ dados.pagamento.tributos.inss * 100 + "%" }}</span></p>
            </td>
          </tr>
          <tr>
            <td>
              <p><strong>CSLL:</strong> <span ng-if="dados.pagamento.tributos.csll > 0">{{ dados.pagamento.tributos.csll * 100 + "%" }}</span></p>
            </td>
          </tr>
        </table>
      </div>
    </section> 
  </div>
</page>
