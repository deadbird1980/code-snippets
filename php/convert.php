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
        $activities[] = $activity;
      } else if (strpos($content->type, 'DTOSectionPartitionAcademicTalkITP') != false || strpos($content->type, 'DTOSectionPartitionExtendedConversationITP') != false) {
        $q_index = 0;
        foreach($content->data->contents as $c) {
          if (strpos($c->type, 'DTOQuestionUnitAcademicTalkITP') == false && strpos($c->type, 'DTOQuestionUnitExtendedConversationITP') == false) {
            continue;
          }
          $activity = array();
          $activity['activity_name'] = 'shared_audio_one_of_many_text';
          $prompt = array();
          $stimulus = array();
          $questions = array();
          $prompt['shuffle'] = false;

          // add master data into stimulus.instruction
          $stimulus['instruction'] = 'Listen to the conversation and answer the questions.';
          $stimulus['audio'] = array("transcript"=>"*", "source"=>$c->audio->data->resource->data->resourceID);
          foreach($c->data->questions as $q) {
            $q = $q->data;
            $question = array();
            $question['shuffle'] = true;
            $question['marker'] = 1;
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
            $question['choices'] = $choices;
            $questions[] = $question;
          }
          $q_index++;
          $prompt['questions'] = $questions;
          $activity['prompt'] = $prompt;
          $activities[] = $activity;
        }
      } else if (strpos($content->type, 'DTOSectionPartitionErrorRecognitionITP') != false || strpos($content->type, 'DTOSectionPartitionSentenceCompletionITP') != false) {
        $q_index = 0;
        foreach($content->data->contents as $c) {
          if (strpos($c->type, 'DTOQuestionUnitErrorRecognitionITP') == false && strpos($c->type, 'DTOQuestionUnitSentenceCompletionITP') == false) {
            continue;
          }
          $is_sentence_completion = true;
          if (strpos($c->type, 'DTOQuestionUnitSentenceCompletionITP') == false) {
            $is_sentence_completion = false;
          }
          $activity = array();
          $activity['activity_name'] = 'text_with_gap_one_of_many';
          $prompt = array();
          $questions = array();
          $prompt['shuffle'] = false;
          if ($is_sentence_completion) {
            $prompt['instruction'] = 'Complete the sentence.';
          } else {
            $prompt['instruction'] = 'Identify the word or phrase that must be changed in order for the sentence to be correct.';
          }

          // add master data into stimulus.instruction
          foreach($c->data->questions as $q) {
            $q = $q->data;
            $question = array();
            $question['shuffle'] = true;
            $question['marker'] = 1;
            $choices = array();
            $question['explanation'] = $q->explanation->data->text->data->studyLanguage;
            $question['question'] = array("text" => $q->sentence);
            if (!$is_sentence_completion) {
              preg_match_all('/\[([^\]]+)\]/', $q->sentence, $matches);
              $question['text'] = preg_replace('/\[([^\]]+)\]/', '[*\1]', $q->sentence);
            }
            $correct = $q->correctAnswer->data->optionIdentifier;
            foreach($q->options as $inx => $option) {
              $choice = array();
              $choice['text'] = $option->data->text? $option->data->text : $matches[1][$inx];
              if ($correct == $option->data->identifier) {
                $choice['correct'] = true;
              } else {
                $choice['correct'] = false;
              }
              $choices[] = $choice;
            }
            $question['choices'] = $choices;
            $questions[] = $question;
          }
          $q_index++;
          $prompt['questions'] = $questions;
          $activity['prompt'] = $prompt;
          $activities[] = $activity;
        }
      }
    }
    $stage['activities'] = $activities;
    $section['stages'][] = $stage;
    $sections[] = $section;
  }
  $lesson['sections'] = $sections;
  print json_encode($lesson);
}
convert();

?>
