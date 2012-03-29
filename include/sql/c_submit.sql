create table submit
(
sid int unsigned auto_increment not null,
pid int unsigned not null default 0,
uid int unsigned not null default 0,
lang int default 0,
result text default '',
score int default 0,
memory int default 0,
accepted int default 0,
subtime int default 0,
IP varchar(24) not null default '',
runtime int default 0,
srcname varchar(256) not null default '',
primary key (sid),
foreign key (pid) references problem(pid),
foreign key (uid) references userinfo(uid)
)
