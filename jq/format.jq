def format: . as $interval|($interval/60/60|floor) as $hour|($interval-$hour*60*60) as $minutes|($minutes/60|floor) as $min|($minutes-$min*60) as $sec|if $hour>0 then ($hour|tostring)+" h" else "" end+ if $min>0 then " "+($min|tostring)+"m" else "" end + if $sec>0 then " "+($sec|tostring)+"s" else "" end; format