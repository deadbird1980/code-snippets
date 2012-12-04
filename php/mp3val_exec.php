<?php
$file = $_SERVER['argv'][1];
$command = "/usr/local/bin/mp3val {$file}|grep unlikely";
exec($command, $output, $return_var);
if (!$return_var) {
    print "{$data['id']} has a problem with filename={$data['filename']}\n";
}
?>
