#!/bin/bash - 
set -o nounset                              # Treat unset variables as an error
usage ()
{
     echo "clean"
     echo "usage: clean directory"
     echo "list unused files"
}

# test if we have one arguments on the command line
if [ $# != 1 ]
then
    usage
    exit
fi

TMP="/tmp"
find $1/resource/file -name *.pdf -o -name *.jpg -o -name *.swf -o -name *.mp3 -o -name *.png | sed "s:$1/resource/::" > $TMP/all
grep -o --no-filename 'file[^"]*\.\(pdf\|jpg\|swf\|mp3\)' $1/json/*.json| uniq | sed -e 's:\\/:/:g' >  $TMP/used
fgrep -v $TMP/all -f $TMP/used
rm $TMP/used
rm $TMP/all

