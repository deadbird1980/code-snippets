<?php
function convert() {

  $text = file_get_contents('test1.json');

  $lesson = array("band"=>3, "level"=>3, "type"=>"toefl", "category"=>"Tests");
  $json = json_decode($text);
  $secs = $json->data->sections;
  $lesson['name'] = $json->data->testName;
  $sections = array();
  $stages = array();
  $section_titles = array("Section 1: Listening Comprehension", "Section 2: Structure and Written Expression", "Section 3: Reading Comprehension");
  $stage_titles = array("Listening Comprehension I", "Structure and Written Expression", "");
  foreach($secs as $sec_index => $sec) {
    $section = array("stages"=>array());
    $section['title'] = $section_titles[$sec_index];
    $stage = array();
    $stage['assessment_type'] = "practice";
    $stage['title'] = $stage_titles[$sec_index];
    $activities = array();
    $contents = $sec->data->contents;
    foreach($contents as $content) {
      if (strpos($content->type, 'TOEFL.itp.') == false) {
        continue;
      }
      //sectionListening::DTOSectionPartitionMiniDialogITP
      $activity = array();
      if (strpos($content->type, 'DTOSectionPartitionMiniDialogITP') != false) {
        $activity['activity_name'] = 'audio_one_of_many_text';
        $prompt = array();
        $questions = array();
        $prompt['shuffle'] = false;

        $q_index = 0;
        foreach($content->data->contents as $c) {
          if (strpos($c->type, 'DTOQuestionUnitMiniDialogITP') == false) {
            continue;
          }
          $q = $c->data->questions[0]->data;
          $question = array();
          $question['shuffle'] = true;
          $question['marker'] = 1;
          $stimulus = array();
          $choices = array();
          $question['explanation'] = $q->explanation->data->text->data->studyLanguage;
          $correct = $q->correctAnswer->data->optionIdentifier;
          foreach($q->options as $option) {
            $choice = array();
            $choice['text'] = $option->data->text;
            if ($correct == $option->data->identifier) {
              $choice['correct'] = true;
            } else {
              $choice['correct'] = false;
            }
            $choices[] = $choice;
          }
          $stimulus['instruction'] = 'Listen to these excerpts from the conversation and answer the questions about references.';
          $stimulus['audio'] = $c->data->audio->data->resource->data->resourceID;
          $question['stimulus'] = $stimulus;
          $q_index++;
          $question['question'] = array("text" => "Question $q_index");
          $question['choices'] = $choices;

          $questions[] = $question;
        }
        $prompt['questions'] = $questions;
        $activity['prompt'] = $prompt;
      }
      $activities[] = $activity;
    }
    $stage['activities'] = $activities;
    $section['stages'][] = $stage;
    $sections[] = $section;
  }
  $lesson['sections'] = $sections;
  print_r($lesson['sections'][0]['stages'][0]['activities'][0]);
}
convert();

?>
