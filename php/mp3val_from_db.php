<?php
$dbconn = pg_connect('host=localhost port=5432 dbname=db user=pgsql');
$sql = "select id, mp3_data, filename from file where filename like '%_part%' order by id desc limit 10";
$res = pg_query($dbconn, $sql);
while($data = pg_fetch_array($res)) {
    $tmpfname = tempnam("/tmp", $data['id']);
    $f = fopen($tmpfname, "w");
    fwrite($f, stripcslashes($data['data_mp3']));
    fclose($f);
    $return_var = 0;
    $output = array();
    $command = "/usr/local/bin/mp3val {$tmpfname}|grep unlikely";
    exec($command, $output, $return_var);

    unlink($tmpfname);
    // $ret is 0 when it success
    if (!$return_var) {
        print "{$data['id']} has a problem with filename={$data['filename']}\n";
    }
}
?>
