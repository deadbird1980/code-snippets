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
if [ ! -d "$1" ]
then
    echo "Directory $1 does not exist!"
    exit
fi
SOURCE_DIR="$1"

TMP="/tmp"
find $SOURCE_DIR/resource/file -type f \( -name "*.pdf" -or -name "*.jpg" -or -name "*.swf" -or -name "*.mp3" -or -name "*.png" \) | sed "s:$SOURCE_DIR/resource/::" > $TMP/all
grep -o --no-filename 'file[^"]*\.\(pdf\|jpg\|swf\|mp3\)' $SOURCE_DIR/json/*.json| uniq | sed -e 's:\\/:/:g' >  $TMP/used
fgrep -v $TMP/all -f $TMP/used > $TMP/unused
rm $TMP/used
rm $TMP/all

