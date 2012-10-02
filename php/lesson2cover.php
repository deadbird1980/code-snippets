<?php
require_once('Zend/Json.php');
function usage() {
  print "Usage: php -d include_path=zend lesson2Cover.php [command].... source \n";
  exit;
}

if (count($_SERVER['argv']) == 1) {
  usage();
  exit;
}

//a template flashUIURL
function getTemplates($tree) {
  $fnd = array();
  if (is_array($tree)) {
    if (isset($tree['data']) &&isset($tree['type'])) {
      $fnd = array_merge($fnd, getTemplates($tree['data']));
    } else {
      foreach($tree as $key=>$val) {
        if ($key == 'flashUIURL') {
          if (isset($val['data']['resourceID']) && strlen($val['data']['resourceID'])>4) {
            $fnd[] = $val['data']['resourceID'];
          }
        } else {
          $fnd = array_merge($fnd, getTemplates($val));
        }
      }
    }
  }
  $fnd = array_unique($fnd);
  return $fnd;
}

$pattern = 'lesson_*.json';
$src = $_SERVER['argv'][1];
if (in_array("-p", $_SERVER['argv'])) {
  $key = array_search("-p", $_SERVER['argv']);
  $pattern = $_SERVER['argv'][$key+1];
}

$lessonTemplates = array();
$lessonList = array();

$files = glob("$src/$pattern");
foreach($files as $file) {
  $json = file_get_contents($file);
  $template = Zend_Json::decode($json);
  // lesson name
  if (!isset($template['data']['_resource_id'])) {
    continue;
  }
  $lesson_id = $template['data']['_resource_id'];
  $lesson = array();
  if (isset($template['data']['name'])) {
    $lesson['name']=array('studyLanguage'=>
                        $template['data']['name']['data']['studyLanguage'],
                        'userLanguage'=>
                        $template['data']['name']['data']['userLanguage']);
  }
  if (isset($template['data']['name'])) {
    $lesson['category'] = $template['data']['category']['data']['studyLanguage'];
  }
  $lessonList[$lesson_id] = $lesson;
  $lessonTemplates[$lesson_id] = getTemplates($template);
}

print "lessonTemplates=".count($lessonTemplates)."\n";
$templateLessons = array();
foreach($lessonTemplates as $lesson_id => $templates) {
  foreach($templates as $template) {
    if (!isset($templateLessons[$template])) {
      $templateLessons[$template] = array();
    }
    $templateLessons[$template][] = $lesson_id;
  }
}
print "templates=".count($templateLessons)."\n";

asort($templateLessons);
$lessons2Cover = array();
$templatesCovered = array();
foreach($templateLessons as $template => $lessons) {
  if (!in_array($template, $templatesCovered)) {
    $templatesDiff = 0;
    foreach($lessons as $lesson) {
      $diff = array_diff($lessonTemplates[$lesson], $templatesCovered);
      if ($diff > $templatesDiff) {
        $templatesDiff = $diff;
        $lesson2merge = $lesson;
      }
    }
    $lessons2Cover[] = $lesson2merge;
    $templatesCovered = array_merge($lessonTemplates[$lesson2merge], $templatesCovered);
  }
}

print "lesson_id, category, lesson_name, lesson_name2\n";
foreach($lessons2Cover as $lesson) {
  $cateory = '';
  if (isset($lessonList[$lesson]['category'])) {
    $cateory = $lessonList[$lesson]['category'];
  }
  $name = ',';
  if (isset($lessonList[$lesson]['name'])) {
    $name = "{$lessonList[$lesson]['name']['studyLanguage']}, {$lessonList[$lesson]['name']['userLanguage']}";
  }

  print "$lesson,$cateory,$name\n";
}

?>
