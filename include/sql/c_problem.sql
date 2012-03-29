create table problem
(
pid int unsigned auto_increment not null,
probname varchar(24) not null default '' unique,
filename varchar(24) not null default '' unique,
detail text default '',
readforce smallint not null default 0,
submitable tinyint not null default 1,
lastacid int unsigned not null default 1,
addtime int default 0,
addid int unsigned not null default 1,
datacnt int default 0,
submitcnt int default 0,
acceptcnt int default 0,
slureadforce int default 0,
sludetail text default '',
timelimit int default 1000,
difficulty int default 0,
memorylimit int default 1000,
plugin int default 1000,
primary key (pid),
foreign key (lastacid) references userinfo(uid),
foreign key (addid) references userinfo(uid)
)
