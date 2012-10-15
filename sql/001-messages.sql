\c stcoll stagesadm

drop table if exists messages;
drop sequence if exists seq__messages__id;

create sequence seq__messages__id;
create table messages (
       id    	bigint,
       id_m2	bigint references m2(id),
       sender	bigint references users(id),
       datepub  timestamp default CURRENT_TIMESTAMP,
       subject	text,
       message	text,
       msgread	boolean default false
);
alter sequence seq__messages__id owned by messages.id;
alter table messages alter column id set default nextval('seq__type_offre__id');

grant usage on sequence seq__messages__id to stagesweb;
grant select, insert, delete, update on messages to stagesweb;