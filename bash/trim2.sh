for f in *.mp4
do
  #d=4 means only black scenes longer than 4 seconds are detected
  #pic_th=0.98 is the threshold for considering a picture as "black" (in percent)
  #pix=0.15 sets the threshold for considering a pixel as "black" (in luminance). Since you have old 
  ffmpeg -i "$f"  -vf blackdetect=d=0:pic_th=0.077:pix_th=0.90 -an -f null - 2>&1|grep blackdetect > blackdetect.txt
  cat blackdetect.txt
  start=`head -n 1 blackdetect.txt|sed 's/.*black_start:\([0-9.]*\).*/\1/'`
  end=`head -n 1 blackdetect.txt|sed 's/.*black_end:\([0-9.]*\).*/\1/'`
  duration=`head -n 1 blackdetect.txt|sed 's/.*black_duration:\([0-9.]*\).*/\1/'`
  if [[ $duration < 0.1 ]]
  then
    start=$end
  fi
  to=`tail -n 1 blackdetect.txt|sed 's/.*black_end:\([0-9.]*\).*/\1/'`
  echo "$f, $start, $duration, $to"
  echo "$f, $start, $duration, $to" >> result.txt
  ffmpeg -i "$f" -ss $start -to $to -codec copy "trimmed/$f"
done
