<?php
set_time_limit(300); 

// URL from where we fetch its HTML
$programs = array(
  '利津故事' => "http://www.ljgdw.com/tndb/class/",
  '直播民生' => "http://www.ljgdw.com/wzxw/class/",
  '少年梦想秀' => "http://www.ljgdw.com/hyfc/class/",
  '百姓梦想秀' => "http://www.ljgdw.com/whlj/class/",
  '百姓说天气和七色光'=> "http://www.ljgdw.com/fhczy/class/"
);

function parse_program($url, $count=5) {
  //Perform a cURL Session
  $result = file_get_contents ($url);

  //<a href="../html/?5576.html" class="newsquery" target="_self">2014年11月2日 星期日《利津新闻》</a>
  preg_match_all('|<a href="([^"]+)" class="newsquery" target="_self"[ ]+><li>([^<]+)</a>|U', $result, $matches);
  $pages = array();
  $matches;
  $i = 0;
  foreach($matches[1] as $key => $href) {
    $pages[$matches[2][$key]] = $url.$href;
    $i++;
    if ($i > $count) {
      break;
    }
  }
  foreach($pages as $key => $page) {
    $result = file_get_contents ($page);
    //<input type="text" name="filepath" value="http://shiping.ljgdw.com/flv/lj/11.03故事.flv" style="height:1px;" />
    preg_match_all('|<input type="text" name="filepath" value="([^"]+)"|U', $result, $matches);
    if (count($matches[1]) > 0) {
      $pages[$key] = $matches[1][0];
    }
  }
  return $pages;
}

foreach($programs as $url) {
  $files = parse_program($url);
  print json_encode($files);
}
?>
