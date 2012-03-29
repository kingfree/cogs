create table compbase
(
cbid int unsigned auto_increment not null,
cname varchar(24) not null default '' unique,
contains text default '',
ouid int unsigned not null default 0,
primary key (cbid),
foreign key (ouid) references userinfo(uid)
)