<?php
$dbconn = pg_connect('host=localhost port=5432 dbname=db user=pgsql');
$escaped = pg_escape_bytea($data);
$sql = 'select id, key,filename, data_mp3 from file where id=43612 order by id';
$res = pg_query($dbconn, $sql);
while ($data = pg_fetch_array($res)) {
  $filename = $data['id']."_".$data['filename'];
  $f = fopen($filename, 'w');
  fwrite($f, stripcslashes($data['data_mp3']));
  fclose($f);
}

?>
