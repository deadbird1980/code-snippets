set -o nounset                              # Treat unset variables as an error
jq 'group_by(.course_id+.created[0:16])|[.[]|min_by(.submitted) as $min|{"course_id":.[0].course_id, "created":.[0].created, "length":length, "delay":$min.delay,"submitted":$min.submitted}]' attempt.json > real.json
jq '[.[]|select(.course_id=="651222" and .created[0:13]=="2016-01-26T08").created]|sort_by(.)' attempt.json
jq '60*5 as $adjust|[.[]|if .delay>-$adjust and .delay<$adjust then 0 else .delay/(60*60*24)|floor+1 end]|unique|length' real.json
jq '60 as $adjust|[.[]|select(.delay>$adjust and .delay<60*60)|.day=if .delay>-$adjust and .delay<$adjust then 0 else ((.delay/(60))|floor+1) end]|group_by(.day)|[.[]|{key:.[0].day|tostring,value:length}]|from_entries' attempt.json > attempt/attempt_result_1hour.json
jq '60 as $adjust|[.[]|select(.delay>$adjust and .delay<60*60*24)|.day=if .delay>-$adjust and .delay<$adjust then 0 else ((.delay/(60*60))|floor+1) end]|group_by(.day)|[.[]|{key:.[0].day|tostring,value:length}]|from_entries' attempt.json > attempt/attempt_result_1day.json

