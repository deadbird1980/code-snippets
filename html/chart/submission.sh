set -o nounset                              # Treat unset variables as an error
units=(1 2 5 10 20)
#jq '[.[]|.course_id as $course_id|.submissions[]|select(.point>0)|{"course_id":$course_id, "point":.point}]' -c submission.json > submission_flatten_simple.json
for i in "${units[@]}"
do
  echo $i
  #bash -c "jq '${i} as \$unit|group_by(.point/\$unit|floor)|[.[]|{key:(.[0].point/\$unit|floor*\$unit|tostring),value:length}]|from_entries' submission_flatten_simple.json" > "submission_result_$i".json
done

#jq '[.[]|select(.duration==15724800)|.course_id as $course_id|.submissions[]|select(.point>0)|{"course_id":$course_id, "point":.point}]' -c submission.json > submission_flatten_6months.json
#jq '[.[]|select(.duration==28425600)|.course_id as $course_id|.submissions[]|select(.point>0)|{"course_id":$course_id, "point":.point}]' -c submission.json > submission_flatten_11months.json
28425600

for i in "${units[@]}"
do
  echo $i
  bash -c "jq '${i} as \$unit|group_by(.point/\$unit|floor)|[.[]|{key:(.[0].point/\$unit|floor*\$unit|tostring),value:length}]|from_entries' submission_flatten_11months.json" > "submission_result_11months_$i".json
done

