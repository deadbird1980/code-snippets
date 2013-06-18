<?php
function usage() {
  print "Usage: php lesson_pattern.php [command].... source \n";
  exit;
}

if (count($_SERVER['argv']) == 1) {
  usage();
  exit;
}
function cmp($a, $b) {
  return $b['count'] - $a['count'];
}

//a template flashUIURL
function getTemplates($lesson) {
  $templates = array();
  if (isset($lesson->data->sections)) {
    $sections = $lesson->data->sections;
  } else {
    return '';
  }

  foreach($sections as $section) {
    $stages = $section->data->stages;
    foreach($stages as $stage) {
      $type = $stage->data->activity->type;
      $activity = $stage->data->activity;
      switch ($type) {
        case 'com.re.lib.template.dto.project.PE4::DTOMPreContainer':
          $type = $activity->data->readingData->data->media->type . '+' . $activity->data->questions->data->activityData->type;
          break;
        case 'com.re.lib.template.dto.project.PE4::DTOMQxQContainer':
          $type = $activity->data->readingData->data->media->type . '+' . $activity->data->questions->data->activityData->type;
          break;
        case 'com.re.lib.template.dto.project.PE4::DTOLessonTestContainer':
          $type = $activity->data->lessonTest->type;
          break;
      }

      $templates[] = str_replace('com.re.lib.template.dto.project.PE4::','',$type);
    }
  }
  return implode(',', $templates);
}

$pattern = 'lesson_[0-9]*.json';
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
  $template = json_decode($json);
  // lesson name
  if (!isset($template->data->_resource_id)) {
    continue;
  }
  $lesson_id = $template->data->_resource_id;
  $lesson = array();
  if (isset($template->data->name)) {
    $lesson['name']=array('studyLanguage'=>
                        $template->data->name->data->studyLanguage,
                        'userLanguage'=>
                        $template->data->name->data->userLanguage);
  }
  if (isset($template->data->name)) {
    $lesson['category'] = $template->data->category->data->studyLanguage;
  }
  $lessonList[$lesson_id] = $lesson;

  $pattern = getTemplates($template);
  if (strlen(trim($pattern)) > 0) {
    $lessonTemplates[$lesson_id] = getTemplates($template);
  }
}

asort($lessonTemplates);
$patterns = array();
$pattern = '';
$first = true;
foreach($lessonTemplates as $lesson_id => $templates) {
  if (!$first && $pattern != $templates) {
    $patterns[] = array('pattern'=>$pattern, 'lessons'=>$lessons, 'count'=>count($lessons));
    $lessons = array();
    $pattern = $templates;
  } else {
    $pattern = $templates;
    $first = false;
  }
  $lessons[] = $lesson_id;
}
$patterns[] = array('pattern'=>$templates, 'lessons'=>$lessons, 'count'=>count($lessons));

usort($patterns,'cmp');
print "lesson_count,pattern,lessons\n";
foreach($patterns as $pattern) {
    print "{$pattern['count']},\"{$pattern['pattern']}\",\"".implode(',', $pattern['lessons'])."\"\n";
}

?>
