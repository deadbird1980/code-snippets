#!/usr/bin/env python2.7
# -*-coding:utf-8 -*
import urllib
import os.path
import re
import pycurl
import time
from optparse import OptionParser
from datetime import datetime
from datetime import date
from datetime import timedelta
from download import Download

parser = OptionParser()
parser.add_option('-p','--program', help='program name')
parser.add_option('-c','--download-count', help='number of download',default=3)
parser.add_option('-d','--days', help='last n days', dest='days', default=1)
parser.add_option('-o','--destination', help='Download directory', default='')
parser.add_option('-n','--dry-run', action="store_true", dest="dry_run", default=False, help='Dry run')
(options, args) = parser.parse_args()
urls = ['http://www.ljgdw.com/wzxw/class/','http://www.ljgdw.com/whlj/class/', 'http://www.ljgdw.com/hyfc/class/', 'http://www.ljgdw.com/tndb/class/']
if options.program:
  start = int(options.program)
  urls = urls[start:start+1]

start_date = (datetime.today() - timedelta(days=int(options.days))).date()

for url in urls:
  f = urllib.urlopen(url)
  s = f.read()
  f.close()
#<td class="newsquery"  style="padding-left:5px"><a href="../html/?5616.html" class="newsquery" target="_self"   ><li>2014年11月13日 《直播民生》 </a></td>
#<td  class="newsquerytime" align=center >2014-11-14</td>
  pages = re.findall('<a href="([^"]+)" class="newsquery" target="_self"[ ]+><li>([^<]+)<\/a><\/td>[^<]+<td  class="newsquerytime" align=center >([^<]+)<\/td>', s, re.MULTILINE)
  i = 0
  for page in pages:
    i = i + 1
    if i > int(options.download_count):
      break
    posted = datetime.strptime(page[2], '%Y-%m-%d').date()
    if posted < start_date:
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
      if options.dry_run:
        continue
      flvurl = urllib.quote_plus(flvurl, safe=':/')
      d = Download(flvurl, options.destination)
      d.start()

      while 1:
        try:
          progress = d.progress['percent']
          print("%.2f percent | %.2f of %.2f" % (progress, d.progress['downloaded'], d.progress['total']))
          if progress == 100:
            print("")
            print("Download complete: %s" % d.filename)
            break
          time.sleep(10)

        # tell thread to terminate on keyboard interrupt,
        # otherwise the process has to be killed manually
        except KeyboardInterrupt:
          d.cancel()
          break

        except:
          raise
      
