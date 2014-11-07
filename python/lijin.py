import urllib
import re
urls = ['http://www.ljgdw.com/tndb/class/']
#urls = ['http://www.ljgdw.com/tndb/class/', 'http://www.ljgdw.com/wzxw/class/']
for url in urls:
  f = urllib.urlopen(url)
  s = f.read()
  f.close()
  pages = re.findall('<a href="([^"]+)" class="newsquery" target="_self"[ ]+><li>([^<]+)<\/a>', s)
  for page in pages:
    f = urllib.urlopen(url + page[0])
    s = f.read()
    f.close()
    flv = re.search('<input type="text" name="filepath" value="([^"]+)"', s)
    if flv:
      print page[1] + '   ' + flv.group(1)

