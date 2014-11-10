#!/usr/bin/env python2.7
# -*-coding:utf-8 -*
import urllib
import re
import pycurl
import argparse
from datetime import date
parser = argparse.ArgumentParser(description='ADD YOUR DESCRIPTION HERE')
parser.add_argument('-d','--date', help='Program date',required=False)
parser.add_argument('-o','--destination', help='Download directory',required=False)
parser.add_argument('-n','--dry-run', action="store_true", default=False, help='Dry run',required=False)
args = parser.parse_args()
d = date.yesterday().strftime('%Y%m%d')
#url = 'http://res.audio.rbc.cn/mp3/xwgb/dcxs/dcxs2014110917205503.wma'
url = 'http://audio.rbc.cn/play.form?programId=1413&start='+d+'172000'

f = urllib.urlopen(url)
s = f.read()
f.close()
audio = re.search("var url = '(http://res.audio.rbc.cn/mp3/[^.]+.wma)';", s)
if audio:
  url = audio.group(1)
  if url:
    url = url.replace('wma', 'mp3')
    if args.dry_run:
      print 'Downloading ' + url
      exit(-1)
    #download the file
    fp = open((url.rsplit('/',1))[1], "wb")
    curl = pycurl.Curl()
    url = urllib.quote(url).replace('%3A', ':')
    curl.setopt(pycurl.URL, url)
    curl.setopt(pycurl.WRITEDATA, fp)
    curl.perform()
    curl.close()
    fp.close()
