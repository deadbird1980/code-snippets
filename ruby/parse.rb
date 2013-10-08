require 'json'
file= File.read('./test.json')
def event(obj)
  case obj
  when Hash
    if obj['data'] && obj['type']
      obj.merge! obj['data']
      obj.delete 'data'
      case obj['type']
      when /DTOMultipleString/
        #obj.merge! obj['data']
        #puts obj
      end
      obj.delete 'flashUIUrl'
    end
  end
end

#JSON.parse(file, Proc.new { |object| convert(object)})
rtn = JSON.load(file, Proc.new { |object| event(object)})
def convert

end
puts rtn.to_json

