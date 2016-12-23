set -o nounset                              # Treat unset variables as an error
if [ "$#" -ne 1 ]; then
  echo "Illegal number of parameters"
  exit
fi
url=${1%/}
file=${url##http:*/}
agent='User-Agent: Mozilla/5.0 (iPad; CPU OS 8_4_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12H321 Safari/600.1.4'
#options=" -H 'Connection: keep-alive' -H 'Cache-Control: max-age=0' --compressed"
options=" --compressed"
referer="$url"
result=$(curl $url |grep -o -E 'http.*.mp4')
result=$(curl -L -H "$agent" -H "Referer: $referer" $options $result -o frame.html)
result=$(cat frame.html|grep -o -E 'http.*.m3u8')
echo $result
if [ -z "$result" ]
then
  echo "no url to fetch"
else
  youtube-dl $result -o $file.mp4
fi

