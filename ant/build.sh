#!/bin/sh

if [ "$flex" = "" ]; then
  flex='/usr/local/flex_sdk_3'
fi

if [ ! -d "build" ]; then
  mkdir build
fi

if [ "$output" = "" ]; then
  output='build/RELib'
fi

compc=$flex/bin/compc

$compc -warnings=false -source-path src -include-sources=src -output $output.swc
