#!/usr/bin/env python2.7
# -*-coding:utf-8 -*
import urllib
import re
import pycurl
urls = ['http://www.ljgdw.com/tndb/class/']
#urls = ['http://www.ljgdw.com/tndb/class/', 'http://www.ljgdw.com/wzxw/class/']
for url in urls:
  f = urllib.urlopen(url)
  s = f.read()
  f.close()
  pages = re.findall('<a href="([^"]+)" class="newsquery" target="_self"[ ]+><li>([^<]+)<\/a>', s)
  #pages = [['1.flv', u'http://shiping.ljgdw.com/flv/lj/11.03故事.flv']]
  for page in pages:
    f = urllib.urlopen(url + page[0])
    s = f.read()
    f.close()
    flv = re.search('<input type="text" name="filepath" value="([^"]+)"', s)
    if flv:
      url = flv.group(1)
      #download the file
      fp = open((url.rsplit('/',1))[1], "wb")
      curl = pycurl.Curl()
      url = urllib.quote(url).replace('%3A', ':')
      print page[1] + '   ' + url
      curl.setopt(pycurl.URL, url)
      curl.setopt(pycurl.WRITEDATA, fp)
      curl.perform()
      curl.close()
      fp.close()
    break
