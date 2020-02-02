set -o nounset                              # Treat unset variables as an error

#https://aod-rfi.akamaized.net/rfi/mandarin/audio/magazines/r001/19_00_-_19_15_20191223.mp3
date=$(date "+%Y%m%d")
url="https://aod-rfi.akamaized.net/rfi/mandarin/audio/magazines/r001/19_00_-_19_15_${date}.mp3"
if wget -nc $url -O RFI.mp3; then
 ffmpeg -i RFI.mp3 -b:a 32k 法广${date}.mp3
fi

#youtube-dl -f worstaudio --extract-audio --audio-format mp3 --audio-quality 9 "ytsearch1:网言网事 ${date}" -o ${date}.mp3

#youtube-dl -f worstaudio --extract-audio --audio-format mp3 --audio-quality 9 "ytsearch1:网書齋夜話" -o ${date}.mp3

#https://www.voachinese.com/podcast/video.aspx/?zoneId=1904
d=$(date "+%Y/%m/%d")
path=$(curl https://www.voachinese.com/z/3637/$d 2>&1 | grep -m 1 '<a href=".*" class="img-wrap img-wrap--t-spac img-wrap--size-3"' -o | grep -m 1 -oE '"[^"]+" '|grep -oE '[^"]+')
url="https://www.voachinese.com$path"
audio_url=$(curl -L $url 2>&1 | grep -oE '<a href=.*mp3.*"64 kbps' | grep -oE '".+" ' | grep -oE '[^"]+')
file="djt.mp3"
curl $audio_url -o $file
ffmpeg -i djt.mp3 -b:a 32k djt${date}.mp3
echo $url
date=$(date "+%m/%d")
#youtube-dl -f worstaudio --extract-audio --audio-format mp3 --audio-quality 9 "ytsearch1:时事大家谈 ${date}"
#https://www.voachinese.com/z/2372 焦点对话
path=$(curl https://www.voachinese.com/z/2372/$d 2>&1 | grep -m 1 '<a href=".*" class="img-wrap img-wrap--t-spac img-wrap--size-3"' -o | grep -m 1 -oE '"[^"]+" '|grep -oE '[^"]+')
url="https://www.voachinese.com$path"
audio_url=$(curl -L $url 2>&1 | grep -oE '<a href=.*mp3.*"64 kbps' | grep -oE '".+" ' | grep -oE '[^"]+')
file="jddh.mp3"
curl $audio_url -o $file
ffmpeg -i djt.mp3 -b:a 32k djt${date}.mp3
echo $url
date=$(date "+%m/%d")
