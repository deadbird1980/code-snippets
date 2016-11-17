def html_course
  all_html5_cs=Course.where(lesson_presentation:'html').to_a
  real_cs = all_html5_cs.select{|c| c.user.email[/reallyenglish/].nil? }
  attempted_cs = real_cs.select{|c| c.attempts.where(:guid.not=>/@mobile/).size>0}
  [all_html5_cs.size, real_cs.size, attempted_cs.size]
end

def html_lesson_study(file='')
  lses = LessonSession.collection.aggregate([
{"$group"=>{"_id"=>{"course_id"=>"$course_id"},"count"=>{"$sum"=>1}}},
{"$match" => {"count"=> {"$gte" => 1}}}])
  result = []
  lses.each{|ls|
  	course = Course.where(id:ls['_id']['course_id']).first
    user = course.user
    if user.email[/reallyenglish/].nil?
      result << {member_id:user.auth_system_user_id, email:user.email,course_id:course.member_course_id,lesson_count:ls['count']}
    end
  }
  unless file.empty?
    File.open(file, 'w') do |f|
      f.puts result.to_json
    end
  end
  result
end

def html_lesson_duration(file='', rows=0)
  result = []
  i = 0
  re_user_ids = User.where(email:/reallyenglish|example/).pluck :id
  LessonSession.where(:user_id.nin=>re_user_ids, active:false, :commands.not=>{"$size"=>0},:commands.elem_match=>{:type=>'attempt'}).each{|ls|
    commands = ls.commands.sort
    lesson = ls.lesson
    course = ls.course
    user = course.user

    duration = ls.commands.where(:type=>'activity-complete', :startAt.ne=>nil).map{|c| 
      if c.timestamp && c.startAt
        Time.parse(c.timestamp)-Time.parse(c.startAt.to_s) 
      else
        puts "lesson_session_id: #{ls.id} member_course_id:#{course.member_course_id} lesson_id:#{lesson.cms_id}"
        puts c.inspect
        0
      end
    }.sum
    created_ats = commands.map(&:created_at).compact
    start_to_end = created_ats.max - created_ats.min
    result << {member_id:user.auth_system_user_id, email:user.email,course_id:course.member_course_id, lesson_id:lesson.cms_id, level:lesson.level, category:lesson.category, topic:lesson.topic, lesson_session_id:ls.id, startToEnd:start_to_end,duration:duration}

    if !file.empty? && rows > 0 && result.size > rows
      i++
      File.open(file.gsub('.json', '') + "_#{i}.json", 'w') do |f|
        f.puts result.to_json
      end
      result = []
    end
  }
  unless file.empty?
    File.open(file.gsub('.json', '') + "_#{i}.json", 'w') do |f|
      f.puts result.to_json
    end
  end
  result
end


def lesson_duration(file='', start=1.days.ago, verbose=false)

  result = []
  LessonProgress.where(start_at:(start..Time.now),first_score_at:(start..Time.now)).each{|lp|
    
    lesson = lp.lesson
    course = lp.course
    user = course.user
    
    if course.cms3_project_id==205 && !course.debug_mode && user.email[/reallyenglish/].nil? && user.email[/exmaple/].nil?
      duration = lp.first_score_at.to_i-lp.start_at.to_i
      puts "#{lp.first_score_at} - #{lp.start_at} = #{duration}" if verbose
      if lp.attempts.size > 0
        a = lp.attempts.sort.first
        source = 'flash'
        if !a.guid[/@mobile/].nil?
          source = 'mobile'
        elsif !a.guid[/@html/].nil?
          source = 'html'
        end
      else
        source = 'desktop'
      end
      result << {member_id:user.auth_system_user_id, email:user.email,course_id:course.member_course_id, lesson_id:lesson.cms_id, level:lesson.level, category:lesson.category, topic:lesson.topic, duration:duration, start_at:lp.start_at, first_score_at:lp.first_score_at, guid:a.guid, source:source}
    end
  }
  unless file.empty?
    File.open(file, 'w') do |f|
      f.puts result.to_json
    end
  end
  result
end


def submission(file='', start=1.day.ago)
  result = []
  Course.where(cms3_project_id:205, start_at:(start..Time.now), end_at:(start..Time.now), debug_mode:false).each {|course|
  	  user = course.user
  	  if user && user.email[/reallyenglish/].nil? && user.email[/exmaple/].nil?
  	    duration = course.end_at.to_i - course.start_at.to_i
        result << {member_id:user.auth_system_user_id, email:user.email,course_id:course.member_course_id, start_at:course.start_at, end_at:course.end_at, duration:duration, submissions:course.attempts.map{|a| {lesson_id:a.lesson.cms_id, guid:a.guid, submitted:a.submitted,point:(a.submitted.to_i-course.start_at.to_i)/duration.to_f*100,created:a.created_at}}}
      end
  }

  unless file.empty?
    File.open(file, 'w') do |f|
      f.puts result.to_json
    end
  end
  result
end


def attempts(file='', start=1.day.ago, verbose=false)
  result = []
  rows = {}
  Attempt.where(guid:/@mobile/, submitted:(start..Time.now)).each {|a|
  	course = a.course
  	user = a.course.user

  	if course.cms3_project_id==205 && !course.debug_mode && user.email[/reallyenglish/].nil? && user.email[/exmaple/].nil?
  	  delay = a.created_at - a.submitted
  	  
  	  puts "#{a.created_at}-#{a.submitted} = #{delay}" if verbose
      result << {member_id:user.auth_system_user_id, email:user.email,course_id:course.member_course_id, lesson_id:a.lesson.cms_id, guid:a.guid, submitted:a.submitted,created:a.created_at, delay:delay}
      if a.created_at < a.submitted - 2.minute
  	    delay += 9.hour
  	  end
  	  if delay.abs < 30.second
  	  	delay == 0
  	  end
      days = (delay/(3600*24)).ceil
      rows[days] = rows[days]||0
      rows[days] += 1
    end
  }
  unless file.empty?
    File.open(file, 'w') do |f|
      f.puts result.to_json
    end
  end
  rows
end

#html_lesson_duration '/tmp/html_lesson_duration.json'
#lesson_duration('/tmp/lesson_duration.json', 2.days); 0
#LessonProgress.where(:start_at.ne=>nil,:first_score_at.ne=>nil).where(:first_score_at=>{"$gte"=>"this.start_at"}).size
