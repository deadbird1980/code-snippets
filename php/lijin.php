<?php
//Incluce simple_html_dom.php in your project
include_once('simple_html_dom.php');

//increase the execution time limit
set_time_limit(300); 

// URL from where we fetch its HTML
$programs = array(
  'tndb' => "http://www.ljgdw.com/tndb/class/",
  'wzxw' => "http://www.ljgdw.com/wzxw/class/"
);

function parse_program($url) {
  //Initialize cURL Session
  $ch = curl_init(); 

  /*The URL to fetch. This can also be set when initializing a session with curl_init()*/
  curl_setopt ($ch, CURLOPT_URL, $url);

  /*TRUE to follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive, PHP will follow as many "Location: " headers that it is sent, unless CURLOPT_MAXREDIRS is set)*/
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  /*TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly*/
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

  //Perform a cURL Session
  $result = curl_exec ($ch);

  // Create DOM from URL or file
  //Creates simple_html_dom Object
  $html = new simple_html_dom();

  //Load result into simple_html_dom Object
  $html->load($result);

  $pages = array();
  $count = 3;
  $i = 0;
  // Find all images 
  foreach($html->find('td.newsquery') as $element) {
    $a = $element->find('a', 0);
    $pages[$a->plaintext] = "$url{$a->href}";
    if ($i > $count) {
      break;
    }
  }
  foreach($pages as $key => $page) {
    curl_setopt ($ch, CURLOPT_URL, $page);
    $result = curl_exec ($ch);
    $html->load($result);
    $player = $html->find('input[name="filepath"]');
    if (count($player) > 0) {
      $pages[$key] = $player[0]->value;
      print "$key: {$player[0]->value}\n";
    }
  }
  //Close the cURL Session
  curl_close($ch);
}

foreach($programs as $url) {
  parse_program($url);
}

function save_file($url) {
  //Initialize cURL Session
  $ch = curl_init($url);
  /*Open for writing only; place the file pointer at the beginning of the file        and truncate the file to zero length. If the file does not exist, attempt to create it. And “b” is used for write binary file*/
  $filename = end(explode( "/", $url ));
  $fp = fopen($filename, "wb");

  // The file that the transfer should be written to.
  curl_setopt($ch, CURLOPT_FILE, $fp);

  //Perform a cURL Session
  curl_exec($ch);

  //Close cURL Session
  curl_close($ch);

  //Closes an open file pointer
  fclose($fp);
}
?>
