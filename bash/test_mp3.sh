#!/bin/bash - 

server='http://static.files.com/local/mp3'

files=`find . -name '*.mp3'|sed 's/^\.\///g'`
for file in $files
do
  result=`curl -s -S -f -m 10 -w '%{content_type}' -H "Cache-Control:no-cache" $server/$file -o /dev/null`
  if [ $? -ne 0 ]
  then
    echo $file
  else
    if [ "$result" != "audio/mpeg" ]
    then
      echo $file
    fi
  fi
done
