require 'json'
require 'optparse'

options = {}
OptionParser.new do |opts|
  opts.banner = "Usage: cover.rb [options]"

  opts.on("-v", "--[no-]verbose", "Run verbosely") do |v|
    options[:verbose] = v
  end

  opts.on("-i", "--include [EXISTING]", "include") do |v|
    options[:include] = v
  end
end.parse!

class Array
   def contains_all? other
     other = other.dup
     each{|e| if i = other.index(e) then other.delete_at(i) end}
     other.empty?
   end

   def find_dups
      inject(Hash.new(0)) { |h,e| h[e] += 1; h }.select { |k,v| v > 1 }.collect { |x| x.first }
   end
   # Based on hungryblank's version in the comments
   # see http://www.ruby-forum.com/topic/122008
   def find_dups2
      uniq.select{ |e| (self-[e]).size < self.size - 1 }
   end
   def find_ndups     # also returns the number of items
      uniq.map { |v| diff = (self.size - (self-[v]).size); (diff > 1) ? [v, diff] : nil}.compact
   end
   # cf. http://www.ruby-forum.com/topic/122008
   def dups_indices   
      (0...self.size).to_a - self.uniq.map{ |x| index(x) }
   end
   def dup_indices(obj)
      i = -1
      ret = map { |x| i += 1; x == obj ? i : nil }.compact
      #ret = map_with_index { |x,i| x == obj ? i : nil }.compact
      ret.shift
      ret
   end
   def delete_dups(obj)
      indices = dup_indices(obj)
      return self if indices.empty?
      indices.reverse.each { |i| self.delete_at(i) }
      self
   end
end

file = ARGF.read
data_hash = JSON.parse(file)
i=0
j=1
result = []
lessons = []
if options[:include]
  lessons = JSON.parse(File.read(options[:include]))
  result = data_hash.map{|l| l["activities"] if lessons.include?(l["id"]) }.flatten.compact.uniq
end


fnd = true
while fnd do
  i = 0
  max = 0
  to_merge = []
  while(i<data_hash.size) do
    j = i<data_hash.size-1 ? i+1: i
    while(j<data_hash.size) do
      a = data_hash[i]["activities"]
      b = data_hash[j]["activities"]
      dups = a.dup.concat(b).uniq - result
      if dups.size > max
        to_merge = [data_hash[i], data_hash[j]]
        max = dups.size
      elsif dups.size == 0
        data_hash.delete_at(j)
        data_hash.delete_at(i)
        i -= 1
        break
      end
      j += 1
    end
    i += 1
  end
  if max > 0
    a = to_merge[0]
    b = to_merge[1]
    lessons = lessons.concat([a["id"]].concat([b["id"]]).flatten).uniq
    result = result.concat(a["activities"].concat(b["activities"])).compact.uniq
  else
    break
  end
  if data_hash.size <= 1
    break
  end
end
puts lessons.sort.join(",")
puts "#{lessons.size} lessons to cover #{result.size} activities"
if options[:verbose]
  data = JSON.parse(file)
  fnd = nil
  result.sort.each { |activity|
    data.each { |lesson|
      fnd = lesson; break if lessons.include?(lesson["id"]) && lesson["activities"].include?(activity)
    }
    puts "#{activity} #{fnd["id"]} #{fnd["name"]}"
  }
end
