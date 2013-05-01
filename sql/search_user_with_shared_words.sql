-- version 2
select organization_id,organization_name_en,login, p.member_id, p.id as profile_id
 from ls.wm_profiles p join ls.wm_user_word_list wuwl on wuwl.profile_id=p.id
join ls.wm_user_word_list wuwl2 on wuwl2.profile_id=wuwl.profile_id
and wuwl.member_course_id>wuwl2.member_course_id
join tms.member_course_fact f on f.member_course_id=wuwl.member_course_id
 where exists(select wwlw.word_id from ls.wm_word_list_word wwlw 
    join ls.wm_user_item wui on wui.profile_id=p.id and wwlw.word_id=wui.wm_word_id where wui.state<>0 
    and wwlw.list_id in (wuwl.list_id, wuwl2.list_id) group by wwlw.word_id having count(distinct wwlw.list_id)>1)
or wuwl.list_id=wuwl2.list_id
group by p.id,p.member_id,organization_id, login,organization_name_en
having max(end_date)>now() order by organization_id, login;

-- version 3
select organization_id,organization_name_en,login, p.member_id, email
 from ls.wm_profiles p join ls.wm_user_word_list wuwl on wuwl.profile_id=p.id
join ls.wm_user_word_list wuwl2 on wuwl2.profile_id=wuwl.profile_id
and wuwl.member_course_id>wuwl2.member_course_idjoin tms.member_course_fact f on f.member_course_id=wuwl.member_course_id
 where exists(select wwlw.word_id from ls.wm_word_list_word wwlw 
    join ls.wm_user_item wui on wui.profile_id=p.id and wwlw.word_id=wui.wm_word_id
    join ls.wm_word_list_word wwlw2 on wwlw2.word_id=wwlw.word_id and wwlw.list_id<>wwlw2.list_id where wui.state<>0 
    and wwlw.list_id = wuwl.list_id and wwlw2.list_id=wuwl2.list_id)
or wuwl.list_id=wuwl2.list_id
group by p.id,p.member_id,organization_id, login,organization_name_en,email
having max(end_date)>now() order by organization_id, login;
