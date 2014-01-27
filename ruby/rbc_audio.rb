require 'open-uri'
def fetch(date)
  url="http://audio.rbc.cn/play.form?programId=1413&start=#{date}0920000"
  source = open(url).read
  #puts source[/<title>(.*)<\/title>/, 1]
  "wget " + source[/var url = '(.*)';/,1]
end
puts fetch(Date.today.strftime("%Y%m%d"))
