set -o nounset                              # Treat unset variables as an error
if [ "$#" -ne 1 ]; then
  echo "Illegal number of parameters"
  exit
fi
id=$1
curl "http://www.tudou.com/plcover/$id/" -o $id.html
lid=$(grep -Eo "lid: '[0-9]+'" $id.html|grep -Eo "[0-9]+")
curl 'http://www.tudou.com/plcover/coverPage/getIndexItems.html' --data "lid=$lid&sort=0&desc=false&isCache=true&page=1&pageSize=70" -o $id.json
jq '"http://www.tudou.com/programs/view/"+(.message.items[].code)' $id.json|xargs -L1 you-get

