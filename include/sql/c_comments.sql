create table comments
(
cid int unsigned auto_increment not null,
pid int unsigned not null default 0,
uid int unsigned not null default 0,
detail text default '',
stime int default 0,
showcode tinyint default 0,
primary key (cid),
foreign key (pid) references problem(pid),
foreign key (uid) references userinfo(uid)
)