#!/usr/bin/env python2.7
# -*-coding:utf-8 -*
import urllib
import sys
import os.path
import re
import argparse
import subprocess
parser = argparse.ArgumentParser(description='ADD YOUR DESCRIPTION HERE')
parser.add_argument('-p','--program', help='program name', required=False)
parser.add_argument('-l','--list', action="store_true", default=False, help='list programs',required=False)
parser.add_argument('-x','--proxy', help='proxy', required=False)
parser.add_argument('-c','--download-count', help='number of download',default=3, required=False)
parser.add_argument('-o','--destination', help='Download directory',required=False)
parser.add_argument('-n','--dry-run', action="store_true", default=False, help='Dry run',required=False)
args = parser.parse_args()
urls = [
  'http://www.ljgdw.com/wzxw/class/',#'直播民生'
  'http://www.ljgdw.com/whlj/class/',#'百姓大舞台'
  'http://www.ljgdw.com/hyfc/class/',#
  'http://www.ljgdw.com/minsheng/class/', #'微电影
  'http://www.ljgdw.com/fhczy/class/', #'七色光'
  'http://www.ljgdw.com/tndb/class/'#'利津故事
]
if args.program:
  start = int(args.program)
  urls = urls[start:start+1]
if args.list:
  print urls.join("\n")
  sys.exit("")
for url in urls:
  f = urllib.urlopen(url)
  s = f.read()
  f.close()
  pages = re.findall('<a href="([^"]+)" class="newsquery" target="_self"[ ]+><li>([^<]+)<\/a>', s)
  #pages = [['1.flv', u'http://shiping.ljgdw.com/flv/lj/11.03故事.flv']]
  i = 0
  for page in pages:
    i = i + 1
    if i > int(args.download_count):
      break
    f = urllib.urlopen(url + page[0])
    s = f.read()
    f.close()
    flv = re.search('<input type="text" name="filepath" value="([^"]+)"', s)
    if flv:
      flvurl = flv.group(1)
      #download the file
      file_path = (flvurl.rsplit('/',1))[1]
      print page[1] + '   ' + flvurl
      proxy = ''
      if args.proxy:
        proxy = "-x " + args.proxy + " "
      flvurl = urllib.quote(flvurl).replace('%3A', ':')
      cmdcurl = "curl -C - " + proxy + flvurl + ' -o ' + file_path
      if args.dry_run:
        print cmdcurl
        continue
      p = subprocess.Popen(cmdcurl, shell=True, stderr=subprocess.PIPE)
      while True:
        out = p.stderr.read(1)
        if out == '' and p.poll() != None:
          break
        if out != '':
          sys.stdout.write(out)
          sys.stdout.flush()
