def todate: if .== null then 0 else (.[0:19]+"Z")|fromdate end;[.[]|{email,member_course_id,lesson_name,lesson_id,commands:[.lesson_session.commands|.[]|{type,timestamp,created_at,duration,section,stage,activity,interaction}]|[foreach .[] as $item ([[],[]]; if $item.type=="activity-start" then [[$item],[]] elif ($item.type == "study-time" and $item.duration<0) then [[],(.[0]+[$item])] else [(.[0]+[$item]),[]] end; if ($item.type=="study-time" and $item.duration<0) then .[1] else empty end)|select((.[length-1].timestamp|todate)>=(.[0].timestamp|todate) and (((.[0].timestamp|todate)-(.[0].created_at|todate))|length<150))]}|select(.commands|length>0)]