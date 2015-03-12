for f in *.zip
do
  d=${f/.zip/}
  if [ ! -d "$d" ]
  then
    echo "unzipping $f ..."
    mkdir $d
    tar zxf $f -C $d
  fi
done
