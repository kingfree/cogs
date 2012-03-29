create table experimentstorage
(
esid int unsigned auto_increment not null,
eid int unsigned not null default 0,
uid int unsigned not null default 0,
code text default '',
result text default '',
addtime int default 0,

primary key (esid),
foreign key (eid) references experiment(eid),
foreign key (uid) references userinfo(uid)
)
