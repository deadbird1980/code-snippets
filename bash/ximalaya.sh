set -o nounset                              # Treat unset variables as an error
if [ "$#" -ne 1 ]; then
  echo "Illegal number of parameters"
  exit
fi
url=$1
file=${url: -7}
if [ ! -e $file ]
then
  curl $url|grep -oE '<li sound_id="[0-9]+"'|grep -oE '[0-9]+' > $file
fi
ids=$(cat $file)
download_file="$file.download.sh"
if [ ! -e $download_file ]
then
  for id in $ids
  do
    json_url="http://www.ximalaya.com/tracks/$id.json"
    curl $json_url|jq '"aria2c -o "+.nickname+"-"+(.title|gsub(" ";""))+".m4a "+.play_path_64' >> $download_file
  done
fi
cat $download_file|xargs -L1 bash -cx
