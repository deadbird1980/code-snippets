require 'json'
require 'optparse'

options = {}
OptionParser.new do |opts|
  opts.banner = "Usage: cover.rb [options]"

  opts.on("-v", "--[no-]verbose", "Run verbosely") do |v|
    options[:verbose] = v
  end

  opts.on("-f", "--file FILE", "JSON file") do |v|
    options[:file] = v
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

file_name = options[:file]
file = File.read(file_name)
data_hash = JSON.parse(file)
arr = []
i=0
j=1
while(i<data_hash.size) do
  j = i+1
  while(j<data_hash.size) do
    if data_hash[i]["activities"].contains_all?(data_hash[j]["activities"])
      data_hash.delete_at(j)
      j -= 1
    elsif data_hash[j]["activities"].contains_all?(data_hash[i]["activities"])
      data_hash.delete_at(i)
      i -= 1
      break
    end
    j += 1
  end
  i += 1
end
data_hash.sort! { |x, y| y["activities"].size <=> x["activities"].size }
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
    j = i+1
    while(j<data_hash.size) do
      a = data_hash[i]["activities"]
      b = data_hash[j]["activities"]
      if a.contains_all?(b)
        data_hash.delete_at(j)
        j -= 1
      elsif b.contains_all?(a)
        data_hash.delete_at(i)
        i -= 1
      else
        dups = a.dup.concat(b).uniq - result
        if dups.size > max
          to_merge = [i, j]
          max = dups.size
        end
      end
      j += 1
    end
    i += 1
  end
  if max > 0
    #puts "#{to_merge} #{max}"
    i = to_merge[0]
    j = to_merge[1]
    # merge j to i
    data_hash[i]["id"] = [data_hash[j]["id"]].concat([data_hash[i]["id"]]).flatten
    lessons = lessons.concat(data_hash[i]["id"]).uniq
    data_hash[i]["activities"] = data_hash[i]["activities"].concat(data_hash[j]["activities"]).uniq
    result = result.concat(data_hash[i]["activities"]).uniq
    data_hash.delete_at(j)
  else
    break
  end
  if data_hash.size <= 1
    break
  end
end
puts lessons.sort.inspect
puts "#{lessons.size} lessons to cover #{result.size} activities"
if options[:verbose]
  puts result.inspect
end
