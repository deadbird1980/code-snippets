<?php
/*
 * Average score of a number of tests pass the required score then the student 
 * pass the tests
 *
 */
$score_to_pass = 60;
$tests_to_pass = 5;
$scores = array(100, 30, 10);

rsort($scores);
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
$tests_left = $tests_to_pass - count($scores);
$average_score_to_pass = ($score_to_pass * $tests_to_pass - $sum)/($tests_left);
$final_score = (array_sum($scores) + $tests_left * $average_score_to_pass);
$sum_to_pass = $score_to_pass * $tests_to_pass;
if ($final_score > $sum_to_pass) {
  print "You need to pass $tests_left with average score of $average_score_to_pass\n";
  print "final_score = $final_score, $sum_to_pass";
}

?>
