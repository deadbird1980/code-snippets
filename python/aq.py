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
  ('http://vod.aqbtv.cn/aqxwlb.shtml', '安庆新闻联播'),
  ('http://vod.aqbtv.cn/aqjtbd.shtml','安庆教体报道'),
  ('http://vod.aqbtv.cn/ttzb.shtml','天天直播'),
  ('http://vod.aqbtv.cn/zrgkk.shtml','周日公开课'),
  ('http://vod.aqbtv.cn/jmdb/pngj.shtml','陪你逛街'),
  ('http://vod.aqbtv.cn/tshhk.shtml','听说很好看'),
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
  #<td width='155' height='90'><div align='left'><a href='/2016-11/09/cms322125article.shtml' target='_blank'><img src='/_CMS_NEWS_IMG_/20161109/snap_f242983821994da19042de7e6cb3cb33.jpg' width='135' height='95' border=0 class='imgborder' onerror=this.style.visibility='hidden' alt='2016年11月09日《安庆新闻联播》'></a></div></td>
  pages = re.findall("<div align='left'><a href='([^']+)' target='_blank'><img .* alt='([^']+)'><\/a><\/div>", s)
  i = 0
  for page in pages:
    if args.before and datetime.strptime(page[1].rsplit('_', 1)[1], '%Y%m%d') > datetime.strptime(args.before, '%Y/%m/%d'):
      continue
    if args.after and datetime.strptime(page[1].rsplit('_', 1)[1], '%Y%m%d') < datetime.strptime(args.after, '%Y/%m/%d'):
      continue
    i = i + 1
    if i > int(args.download_count):
      break
    url = 'http://vod.aqbtv.cn/' + page[0]
    if args.verbose:
      print 'open ' + url
    f = urllib.urlopen(url)
    file_path = page[1] + '.mp4'
    s = f.read()
    f.close()
    fileId = re.search("countclick\('-1',(\d+)\);", s).group(1)
    url = 'http://vod.aqbtv.cn:8080/soms4/web/jwzt/player/vod_ipad_player.jsp?fileId=' + fileId
    if args.verbose:
      print 'open ' + url
    f = urllib.urlopen(url)
    s = f.read()
    f.close()
    #<video id="player" src="http://60.175.6.75:9600/vod2/1010/12048/201611/12048_20161111_12048_(132.20_1031.72)_bvs.mp4"
    mp4 = re.search('<video id="player" src="([^"]+)"', s)
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
