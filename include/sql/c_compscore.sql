create table compscore
(
csid int unsigned auto_increment not null,
ctid int unsigned not null default 0,
uid int unsigned not null default 0,
pid int unsigned not null default 0,
subtime int default 0,
lang int default 0,
score int default 0,
result text default '',
primary key (csid),
foreign key (ctid) references comptime(ctid),
foreign key (uid) references userinfo(uid),
foreign key (pid) references problem(pid)
)
