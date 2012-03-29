create table tag
(
tid int unsigned auto_increment not null,
pid int unsigned not null default 0,
caid int unsigned not null default 0,
primary key (tid),
foreign key (pid) references problem(pid),
foreign key (caid) references category(caid)
)