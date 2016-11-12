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
  ('http://www.hhek.cn/xinwenpindao/node_393.shtml', '东营新闻联播'),
  ('http://www.hhek.cn/xinwenpindao/node_489.shtml?id=489', '民生零距离'),
  ('http://www.hhek.cn/xinwenpindao/weiweidaolai_1.shtml?id=2493', '伟伟道来'),
  ('http://www.hhek.cn/gonggongpindao/node_838.shtml?id=838', '生活直通车'),
  ('http://www.hhek.cn/jingpinlanmu/node_925.shtml', '美丽东营'),
  ('http://www.hhek.cn/gonggongpindao/node_972.shtml?id=972', '七色星光')
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
  #<span class="kandianshi_list_title"><a href="/gonggongpindao/2016-11/11/cms135628article.shtml" target="_blank">生活直通车_20161110</a></span>
  pages = re.findall("<span class='kandianshi_list_title'><a href='([^']+)' target='_blank'>([^<]+)<\/a><\/span>", s)
  i = 0
  for page in pages:
    if args.before and datetime.strptime(page[1].rsplit('_', 1)[1], '%Y%m%d') > datetime.strptime(args.before, '%Y/%m/%d'):
      continue
    if args.after and datetime.strptime(page[1].rsplit('_', 1)[1], '%Y%m%d') < datetime.strptime(args.after, '%Y/%m/%d'):
      continue
    i = i + 1
    if i > int(args.download_count):
      break
    url = 'http://www.hhek.cn' + page[0]
    if args.verbose:
      print 'open ' + url
    f = urllib.urlopen(url)
    file_path = page[1] + '.mp4'
    s = f.read()
    f.close()
    fileId = re.search("<script>countclick\('-1',(\d+)\);<\/script>", s).group(1)
    url = 'http://www.hhek.cn/soms4/web/jwzt/player/PlayerJS.jsp?fileId='+fileId
    if args.verbose:
      print 'open ' + url
    f = urllib.urlopen(url)
    s = f.read()
    f.close()
    mp4 = re.search("<video  src='([^']+)'", s)
    if mp4:
      mp4url = mp4.group(1)
      #download the file
      print page[1] + '   ' + mp4url
      proxy = ''
      if args.proxy:
        proxy = "-x " + args.proxy + " "
      mp4url = urllib.quote(mp4url).replace('%3A', ':')
      #cmdaria = "aria2c -c - " + proxy + mp4url + ' -o ' + file_path
      cmdaria = "aria2c -c " + proxy + mp4url + ' -o "' + file_path + '"'
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
