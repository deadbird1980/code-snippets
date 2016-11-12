#!/usr/bin/env python2.7
# -*-coding:utf-8 -*
import urllib
import sys
import os.path
import re
import argparse
import subprocess
from datetime import datetime
parser = argparse.ArgumentParser(description='ADD YOUR DESCRIPTION HERE')
parser.add_argument('-p','--program', help='program name', required=False)
parser.add_argument('-l','--list', action="store_true", default=False, help='list programs',required=False)
parser.add_argument('-x','--proxy', help='proxy', required=False)
parser.add_argument('-c','--download-count', help='number of download',default=3, required=False)
parser.add_argument('-b','--before', help='before date', required=False)
parser.add_argument('-a','--after', help='after date', required=False)
parser.add_argument('-o','--destination', help='Download directory',required=False)
parser.add_argument('-n','--dry-run', action="store_true", default=False, help='Dry run',required=False)
parser.add_argument('-v','--verbose', action="store_true", default=False, help='Verbose',required=False)
args = parser.parse_args()
urls = [
  ('http://www.hfbtv.com/folder1458/folder1624/folder1482/folder1483/', '合肥新闻联播'),
  ('', '')
]
if args.program:
  start = int(args.program)
  urls = urls[start:start+1]
if args.list:
  i = 0
  for k,v in urls:
    print str(i) + '. ' + v + '\t' + k
    i = i + 1
  sys.exit("")
for url,v in urls:
  f = urllib.urlopen(url)
  s = f.read()
  f.close()
  #<li style="text-align:left;"><a title="合肥新闻联播2016.11.11" href="javascript:void(0);" onclick="build_video('http://live2.hfbtv.com/vod/vol1/2016-11-11/50392/50392.flv')">合肥新闻联播2016.11.11</a></li>

  pages = re.findall("<li style=\"text-align:left;\"><a title=\"([^\"]+20[0-9]{2}.[0-9]{2}.[0-9]{2})\" href=\"javascript:void\(0\);\" onclick=\"build_video\('([^']+)'\)\">", s)
  print pages
  i = 0
  for page in pages:
    if args.before and datetime.strptime('20'+page[1].rsplit('20', 1)[1], '%Y.%m.%d') > datetime.strptime(args.before, '%Y/%m/%d'):
      continue
    if args.after and datetime.strptime('20'+page[1].rsplit('20', 1)[1], '%Y.%m.%d') < datetime.strptime(args.after, '%Y/%m/%d'):
      continue
    i = i + 1
    if i > int(args.download_count):
      break
    videourl = page[1]
    file_path = page[0] + '.flv'
    if args.verbose:
      print 'open ' + url
    f = urllib.urlopen(url)
    if videourl:
      #download the file
      print page[1] + '   ' + videourl
      proxy = ''
      if args.proxy:
        proxy = "-x " + args.proxy + " "
      #cmdaria = "aria2c -c - " + proxy + videourl + ' -o ' + file_path
      cmdaria = "aria2c -c " + proxy + videourl + ' -o "' + file_path + '"'
      if args.dry_run:
        print cmdaria
        continue
      p = subprocess.Popen(cmdaria, shell=True, stderr=subprocess.PIPE)
      while True:
        out = p.stderr.read(1)
        if out == '' and p.poll() != None:

          break
        if out != '':
          sys.stdout.write(out)
          sys.stdout.flush()
