#!/usr/bin/env python2.7
# -*-coding:utf-8 -*
import urllib
import os.path
import re
import pycurl
urls = ['http://www.ljgdw.com/wzxw/class/','http://www.ljgdw.com/whlj/class/', 'http://www.ljgdw.com/hyfc/class/', 'http://www.ljgdw.com/tndb/class/']
#urls = ['http://www.ljgdw.com/tndb/class/', 'http://www.ljgdw.com/wzxw/class/']
for url in urls:
  f = urllib.urlopen(url)
  s = f.read()
  f.close()
  pages = re.findall('<a href="([^"]+)" class="newsquery" target="_self"[ ]+><li>([^<]+)<\/a>', s)
  #pages = [['1.flv', u'http://shiping.ljgdw.com/flv/lj/11.03故事.flv']]
  i = 0
  for page in pages:
    i = i + 1
    print url + page[0]
    f = urllib.urlopen(url + page[0])
    s = f.read()
    f.close()
    flv = re.search('<input type="text" name="filepath" value="([^"]+)"', s)
    if flv:
      flvurl = flv.group(1)
      #download the file
      file_path = (flvurl.rsplit('/',1))[1]
      print file_path
      print i
      if not os.path.exists(file_path):
        fp = open(file_path, "wb")
        curl = pycurl.Curl()
        flvurl = urllib.quote(flvurl).replace('%3A', ':')
        print page[1] + '   ' + flvurl
        curl.setopt(pycurl.URL, flvurl)
        curl.setopt(pycurl.WRITEDATA, fp)
        curl.perform()
        curl.close()
        fp.close()
    if i > 4:
      break
