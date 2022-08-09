<?php 

function HttpSuccess($array)
{
  header('Content-type: application/json; charset=utf-8');
  echo json_encode($array);
}

function HttpError($array)
{
  header('Content-type: application/json; charset=utf-8');
  echo json_encode($array);
}