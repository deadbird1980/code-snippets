#!/bin/bash - 
#===============================================================================
#
#          FILE: export_wm.sh
# 
#         USAGE: ./export_wm.sh 
# 
#   DESCRIPTION: 
# 
#       OPTIONS: ---
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: YOUR NAME (), 
#  ORGANIZATION: 
#       CREATED: 07/22/2013 09:53:33 BST
#      REVISION:  ---
#===============================================================================

set -o nounset                              # Treat unset variables as an error
for i in {1..12}
do
    month=`date -v -""$i""m +%Y%m`
    echo $month
    rake wordmine:export period=$month wm_export=/home/joey/wm_$month.csv
done

