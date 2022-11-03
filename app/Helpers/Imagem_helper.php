<?php 

if (!function_exists('base64_to_jpeg')) {
  function base64_to_jpeg($base64_string, $output_file) {
    
    $ifp = fopen( $output_file, 'wb' ); 
    $data = explode( ',', $base64_string );

    fwrite( $ifp, base64_decode( $data[ 1 ] ) );
    fclose( $ifp ); 

    return $output_file; 
  }
}

if (!function_exists('getB64Type')) {
  function getB64Type($str) {
    return substr($str, 11, strpos($str, ';') - 11);
  }
}

if (!function_exists('fileBase64Ext')) {
  function fileBase64Ext($str, $exts) {
    return in_array(getB64Type($str),$exts) ? true: false;
  }
}




