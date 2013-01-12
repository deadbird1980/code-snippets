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
find $1/resource/file -type f \( -name "*.pdf" -or -name "*.jpg" -or -name "*.swf" -or -name "*.mp3" -or -name "*.png" \) | sed "s:$1/resource/::" > $TMP/all
grep -o --no-filename 'file[^"]*\.\(pdf\|jpg\|swf\|mp3\)' $1/json/*.json| uniq | sed -e 's:\\/:/:g' >  $TMP/used
fgrep -v $TMP/all -f $TMP/used
rm $TMP/used
rm $TMP/all

