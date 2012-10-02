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
$src = $_SERVER['argv'][1];
$lessonTemplates = array();
$lessonList = array();

$files = glob("$src/lesson_*.json");
foreach($files as $file) {
  $json = file_get_contents($file);
  $template = Zend_Json::decode($json);
  // lesson name
  $lesson_id = $template['data']['_resource_id'];
  $lesson = array();
  $lesson['name']=array('studyLanguage'=>
                        $template['data']['name']['data']['studyLanguage'],
                        'userLanguage'=>
                        $template['data']['name']['data']['userLanguage']);
  $lesson['category'] = $template['data']['category']['data']['studyLanguage'];
  $lessonList[$lesson_id] = $lesson;
  $sections = $template['data']['sections'];
  foreach($sections as $section) {
    $stages = $section['data']['stages'];
    foreach($stages as $stage) {
      $activity = $stage['data']['activity'];
      if ($activity['type'] == 'com.re.lib.template.dto.project.PE4::DTOMQxQContainer' ||
          $activity['type'] == 'com.re.lib.template.dto.project.PE4::DTOMPreContainer') {
        $readingData = $activity['data']['readingData']['data']['media'];
        $questions = $activity['data']['questions']['data']['activityData'];
        $activity['type'] = $readingData['type'].':'. $questions['type'] ;
      }
      if ($activity['type'] == 'com.re.lib.template.dto.project.PE4::DTOLessonTestContainer') {
        $activity['type'] = $activity['data']['lessonTest']['type'];
      }
      if (!isset($lessonTemplates[$lesson_id])) {
        $lessonTemplates[$lesson_id] = array();
      }
      if (!in_array($activity['type'], $lessonTemplates[$lesson_id])) {
        $lessonTemplates[$lesson_id][] = $activity['type'];
      }
    }
  }
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
    print "$lesson, {$lessonList[$lesson]['category']}, {$lessonList[$lesson]['name']['studyLanguage']}, {$lessonList[$lesson]['name']['userLanguage']}\n";
}

?>
