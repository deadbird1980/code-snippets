#!/bin/bash - 
# find duplicated resources
# find duplicated one within duplicated ones

set -o nounset                              # Treat unset variables as an error
usage ()
{
     echo "unused.sh"
     echo "usage: list unsed files"
     echo "list unused files"
}

# test if we have one arguments on the command line
if [ $# != 1 ]
then
    usage
    exit
fi

TMP="/tmp"
#find $1/resource/file -name *.pdf -o -name *.jpg -o -name *.swf -o -name *.mp3 -o -name *.png | sed "s:$1/resource/::" > $TMP/all
find $1/resource/file -name *.mp3 -o -name *.pdf -o -name *.jpg -o -name *.png -o -name swf > $TMP/all
grep -o '_i_[0-9]\+_' $TMP/all |uniq -d > $TMP/duplicated
grep -o --no-filename 'file[^"]*\.\(pdf\|jpg\|swf\|mp3\)' $1/json/*.json| uniq | sed -e 's:\\/:/:g' >  $TMP/used
fgrep $TMP/all -f $TMP/duplicated > $TMP/all_duplicated
fgrep $TMP/used -f $TMP/duplicated > $TMP/duplicated_used
fgrep -v $TMP/all_duplicated -f $TMP/duplicated_used
rm $TMP/all
rm $TMP/used
rm $TMP/duplicated
rm $TMP/all_duplicated

