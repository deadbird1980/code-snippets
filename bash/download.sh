set -o nounset                              # Treat unset variables as an error
if [ "$#" -ne 1 ]; then
  echo "Illegal number of parameters"
  exit
fi
url=$1
url=${url%/}
i=1
while true; do
  no=$(printf "%02d" $i)
  file=${url/01.mp4/$no.mp4}
  if wget -nc $file; then
    echo 'downloaded!'
  else
    break
  fi
  ((i++))
  echo $i
done

