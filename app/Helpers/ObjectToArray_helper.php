<?php 
if (!function_exists("ObjectToArray")) {
  function ObjectToArray($obj)
  {
    $data = [];
    foreach ($obj as $row) $data[] = json_decode(json_encode($row), true);
    return $data;
  }
}
