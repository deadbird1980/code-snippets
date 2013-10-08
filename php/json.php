<?php
$str = '{"type":"class", "data":{"students":[{"type":"student", "data":{"name":"Jess"}}]}}';
$json = json_decode($str, true);
$convert = function($value, $key) {
    print $key."\n";
  if (isset($value['type']) && isset($value['data'])) {
    $value = $value['data'];
  }
}; 
array_walk_recursive($json, $convert);
print_r($json);

?>
