<?php 

if (!function_exists('GerarContratoDocusign')) {
  function GerarContratoDocusign($payload) {
    try {
      $string = '';

      foreach($payload as $key => $value) {
        if($string != '') $string .= '&';
        $string .= $key.'='.$value;
      }

      $string = str_replace(' ', '%20',$string);

      $ch = curl_init('https://docusign.easymaq.app/public?'.$string );
  
      curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      $result = curl_exec($ch);
      curl_close($ch);

      return json_decode($result,1);
    } catch(\Exception $e) {
      return false;
    }
  }
}

if (!function_exists('formatCnpjCpf')) { 
  function formatCnpjCpf($value)
  {
    $CPF_LENGTH = 11;
    $cnpj_cpf = preg_replace("/\D/", '', $value);
    
    if (strlen($cnpj_cpf) === $CPF_LENGTH) {
      return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
    } 
    
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
  }
}

if (!function_exists('formatRG')) { 
  function formatRG($value)
  {
    $value = preg_replace("/\D/", '', $value);
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{1})/", "\$1.\$2.\$3-\$4", $value);
  }
}