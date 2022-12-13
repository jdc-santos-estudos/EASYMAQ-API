<?php

namespace App\Controllers;

use App\Controllers\API;

use App\Models\Configuracao_model;
use App\Models\Pedido_model;

use Dompdf\Dompdf;

class Contrato extends API
{
  public function showPDF()
  {
    return view('contrato/contrato');
  }

  public function downloadPDF()
  {
    try {

      $file = basename(urldecode($_GET['cd']));
      $fileDir = '/home2/easyma68/contratos/';

      if (file_exists($fileDir . $file.'_assinado.pdf'))
      {
        // Note: You should probably do some more checks 
        // on the filetype, size, etc.
        $contents = file_get_contents($fileDir . $file);

        header("Content-type:application/pdf");

        // It will be called downloaded.pdf
        header("Content-Disposition:attachment;filename=".$file."_assinado.pdf");

        readfile($fileDir . $file.'_assinado.pdf');
      }

    } catch(\Exception $e) {
      echo "ERRO: ";
      print_r($e->getMessage());
    }
  }

  public function docusign() {
    
  }

  public function docusignCallback() {
    try {
      // $myfile = fopen("pedidos.txt", "a") or die("Unable to open file!");
      $data = json_decode(json_encode($this->request->getVar('data')),1);
      $pedidoM =  new Pedido_model();

      $pedidoM->pedidoAssinado($data['envelopeId']);

      // fwrite($myfile, "\n". $txt);
      // fclose($myfile);
      return $this->HttpSuccess([],'callback OK');
    } catch(\Exception $e) {
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno callback do docusign.');
    }
  }
}