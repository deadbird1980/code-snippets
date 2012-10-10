<?php
/*
 * Average score of a number of tests pass the required score then the student 
 * pass the tests
 *
 */

function averageScore2Pass($config, $scores) {
  $score_to_pass = $config['score_to_pass'];
  $tests_to_pass = $config['tests_to_pass'];
  $tests_no = $config['tests_no'];
  print_r($config);

  rsort($scores);
  print_r($scores);
  $i = 0;
  $sum = 0;
  foreach($scores as $score) {
    if ($sum + $score < $score_to_pass * ($i+1)) {
      break;
    }
    $sum += $score;
    $i++;
  }
  $average_passed = $i;
  if ($average_passed < $tests_to_pass) {
    $tests_left = $tests_no - count($scores);
    $tests_required = $tests_to_pass - $average_passed;
    if ($tests_left < $tests_required) {
      for($j=0; $j<$tests_required - $tests_left; $j++) {
        $sum += $scores[$i+$j];
      }
      $tests_required = $tests_left;
    }
    $sum_to_pass = $score_to_pass * $tests_to_pass;
    $average_score_to_pass = ($sum_to_pass - $sum) / $tests_required;
    print "You need to pass $tests_required with average score of $average_score_to_pass\n";
    return $average_score_to_pass;
  } else {
    print "You passed by average score\n";
  }
  return 0;
}

$config = array('tests_no'=>4,
                'score_to_pass'=>60,
                'tests_to_pass'=>1
            );
$scores = array(60);
averageScore2Pass($config, $scores);
$config = array('tests_no'=>4,
                'score_to_pass'=>60,
                'tests_to_pass'=>3
            );
$scores = array(70, 60);
averageScore2Pass($config, $scores);
$scores = array(70, 30);
averageScore2Pass($config, $scores);
$scores = array(70, 30, 10);
averageScore2Pass($config, $scores);
$scores = array(40, 30, 10);
averageScore2Pass($config, $scores);
?>
