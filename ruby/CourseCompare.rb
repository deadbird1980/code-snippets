require 'yajl'
require 'json-compare'
src, dst = 'old', 'new'
files = Dir[File.join(src, 'lesson_*.json')].map{|f| File.basename(f)}
files.each do |file|
  json1 = File.new(File.join(src, file), 'r')
  json2 = File.new(File.join(dst, file), 'r')
  old, new = Yajl::Parser.parse(json1), Yajl::Parser.parse(json2)

  #exclusion = ["noMicrophoneInstruction","buttonLabelScanAgain", "learningStatus", "maxRecordLength", "_lang", "course",
               #"sendToTutorButtonLabel","instructionForSendToTutor", "selfStudyMode", "flashUIURL"]
  #exclusion = ["noMicrophoneInstruction", "_resource_id","buttonLabelScanAgain", "flashUIURL", "maxRecordLength"]
  exclusion = []
  begin
    result = JsonCompare.get_diff(old, new, exclusion)
  rescue
  end
  if !result.nil? && result.size>0
    puts "#{json1.to_path}, #{json2.to_path}"
    puts result.inspect
  end
end

