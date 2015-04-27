for f in *.zip
do
  d=${f/.zip/}
  #if [ true ]
  if [ $f -nt $d ]
  #if [ ! -d "$d" ]
  then
    echo "unzipping $f ..."
    rm -rf $d
    mkdir -p $d
    tar zxf $f -C $d
    touch -r $f $d
  fi
done
