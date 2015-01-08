for f in *.mp4
do
  ffmpeg -i "$f" -ss 0 -t 4 -vn -af silencedetect=n=-30dB:d=0.5 -f null - 2> silencedetect.txt
  start=`grep silencedetect silencedetect.txt|sed -n '3,3p'|cut -d ' ' -f 5`
  if [ "$start" == "" ]
  then
    start=0.5
  fi
  echo "$start $f" >> result.txt
  ffmpeg -i "$f" -ss $start -codec copy "trimmed/$f"
done
