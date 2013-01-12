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

# remove / at the end if exists
SOURCE_DIR="${1%/}"

RESOURCE_DIR="resource"
JSON_DIR="json"
TMP="/tmp"

# find out files under resource with extension as unknown, pdf, jpg, swf, mp3, png
find $SOURCE_DIR/$RESOURCE_DIR -type f \( -name "*.unknown" -or -name "*.pdf" -or -name "*.jpg" -or -name "*.swf" -or -name "*.mp3" -or -name "*.png" \) | sed "s:$SOURCE_DIR/$RESOURCE_DIR/::" > $TMP/all
grep -o --no-filename '"[^"]*\.\(pdf\|jpg\|swf\|mp3\|unknown\)"' $SOURCE_DIR/$JSON_DIR/*.json| uniq |sed -e 's:"::g'| sed -e 's:\\/:/:g' | sort -u| uniq >  $TMP/used
fgrep -v $TMP/all -f $TMP/used > $TMP/unused

rsync -a --remove-source-files $SOURCE_DIR/$RESOURCE_DIR --files-from $TMP/unused $SOURCE_DIR/resource_unused

rm $TMP/all
rm $TMP/used
rm $TMP/unused

