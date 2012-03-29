create table comptime
(
ctid int unsigned auto_increment not null,
cbid int unsigned not null default 0,
intro text default '',
starttime int unsigned not null default 0,
endtime int unsigned not null default 0,
showscore int unsigned not null default 0,
readforce int unsigned not null default 0,
group int not null default 1,
primary key (ctid),
foreign key (cbid) references compbase(cbid)
)