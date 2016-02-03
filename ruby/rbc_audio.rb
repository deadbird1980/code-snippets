require 'open-uri'
require 'date'
def fetch(url, date)
  source = open(url).read
  #puts source[/<title>(.*)<\/title>/, 1]
  "wget " + source[/var url = '(.*)';/,1]
  #jQuery.publish("/player/set_resource", "http://res.audio.rbc.cn/xwgb/dcxs/dcxs2014012717205503.wma");
  "wget " + source[/jQuery.publish\("\/player\/set_resource", "(.*)"\);/,1]
end
date = Date.today.strftime("%Y%m%d")
url = "http://audio.rbc.cn/play.form?programId=1413&start=#{date}0920000"
puts fetch(url, date)
url = "http://audio.rbc.cn/play.form?programId=2943&start=#{date}230000"
puts fetch(url, date)
