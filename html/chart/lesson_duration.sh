set -o nounset                              # Treat unset variables as an error
jq '[.[]|select(.source=="flash")]' lesson_duration_60.json > flash_duration.json
jq 'groupby(.category)|[{key:.[0].cateory, value: }]' flash_duration.json
jq 'group_by(.category)|[.[]|select(length>0)|.[0] as $row|{"key":$row.category, "value": (([.[].duration]|add)/length)}]' -c flash_duration.json > flash_category.json
jq 'group_by(.level)|[.[]|select(length>0)|.[0] as $row|{"key":$row.level, "value": (([.[].duration]|add)/length)}]' -c flash_duration.json > flash_level.json
jq 'group_by(.topic)|[.[]|select(length>0)|.[0] as $row|{"key":$row.topic, "value": (([.[].duration]|add)/length)}]' -c flash_duration.json > flash_topic.json
jq '[.[]|select(.source=="html")]' lesson_duration_60.json > html_duration.json
jq 'group_by(.level)|[.[]|select(length>0)|.[0] as $row|{"key":$row.level, "value": (([.[].duration]|add)/length)}]' -c html_duration.json > html_level.json
jq 'group_by(.category)|[.[]|select(length>0)|.[0] as $row|{"key":$row.category, "value": (([.[].duration]|add)/length)}]' -c html_duration.json > html_category.json
jq 'group_by(.topic)|[.[]|select(length>0)|.[0] as $row|{"key":$row.topic, "value": (([.[].duration]|add)/length)}]' -c html_duration.json > html_topic.json
jq '[.[]|select(.source=="mobile")]' lesson_duration_60.json > mobile_duration.json
jq 'group_by(.topic)|[.[]|select(length>0)|.[0] as $row|{"key":$row.topic, "value": (([.[].duration]|add)/length)}]' -c mobile_duration.json > mobile_topic.json
jq 'group_by(.category)|[.[]|select(length>0)|.[0] as $row|{"key":$row.category, "value": (([.[].duration]|add)/length)}]' -c mobile_duration.json > mobile_category.json
jq 'group_by(.level)|[.[]|select(length>0)|.[0] as $row|{"key":$row.level, "value": (([.[].duration]|add)/length)}]' -c mobile_duration.json > mobile_level.json
