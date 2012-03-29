create table experiment
(
eid int unsigned auto_increment not null,
expename varchar(24) not null default '' unique,
detail text default '',
contains text default '',
readforce smallint not null default 0,
selectforce smallint not null default 0,
addtime int default 0,
addid int unsigned not null default 0,
submitable tinyint not null default 1,
slureadforce int default 0,
sludetail text default '',
primary key (eid),
foreign key (addid) references userinfo(uid)
)
