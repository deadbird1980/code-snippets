#!/usr/bin/ruby -w
require 'time'
counter = 1
previous = 0
time_spent = 0
file = File.new("posts.txt", "r")
while (line = file.gets)
  request = Time.parse(line)
  if counter == 1
    previous = request
  end
  counter += 1
  if request - previous >= 60
    time_spent += request - previous if request - previous < 15*60
    previous = request
  end
end
puts time_spent
file.close
