require 'json'
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

file = File.read('nh_pattens.json')
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
puts data_hash.size
data_hash.sort! { |x, y| y["activities"].size <=> x["activities"].size }
i=0
j=1
result = data_hash[0]["activities"]
lessons = []
while(i<data_hash.size) do
  j = i+1
  max = 0
  to_merge = 0
  while(j<data_hash.size) do
    activities = data_hash[j]["activities"]
    if !result.contains_all?(activities)
      dups = result.dup.concat(activities).uniq
      if dups.size > max
        to_merge = j
        max = dups.size
      end
    else
      data_hash.delete_at(j)
      j -= 1
    end
    j += 1
  end
  if to_merge > 0 && max > 0
  puts "#{to_merge} #{max}"
    lessons<<data_hash[to_merge]["id"] if to_merge > 0
    result = result.concat(data_hash[to_merge]["activities"]).uniq
    data_hash.delete_at(to_merge)
  else
    break
  end
  i += 1
end
puts lessons.inspect
