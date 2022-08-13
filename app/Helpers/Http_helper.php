<?php 

function HttpSuccess($data, $respond)
{
  $response = [
    'status'   => 200,
    'error'    => null,
    'data' => $data
  ];

  return $respond($response);
}

function HttpError($data, $respond, $path)
{
  $response = [
    'status'   => 404,
    'error'    => null,
    'data' => $data
  ];

  return $respond($response);
}