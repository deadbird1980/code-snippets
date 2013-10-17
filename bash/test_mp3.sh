#!/bin/bash 

CURL='/usr/local/bin/curl'

while getopts d:l:f:o: option
do
  case "${option}"
  in
    d) DIRECTORY=${OPTARG};;
    l) URL=$OPTARG;;
    f) FILE=$OPTARG;;
    o) OUTPUT=$OPTARG;;
  esac
done

if [ "$FILE" != "" ]
then
  files=`cat $FILE`
else
  files=`find $DIRECTORY -name '*.mp3'|sed "s:${DIRECTORY}::"`
fi

if [ "$OUTPUT" != "" ]
then
  echo $files > $OUTPUT
fi

for file in $files
do
  result=`$CURL -s -S -f -m 10 -w '%{content_type}' -H "Cache-Control:no-cache" $URL/$file -o /dev/null`
  if [ $? -ne 0 ]
  then
    echo $file
  else
    if [ "$result" != "audio/mpeg" ]
    then
      echo "$URL/$file"
    fi
  fi
done
