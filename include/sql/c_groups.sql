create table groups
(
gid int unsigned auto_increment not null,
gname varchar(24) not null default '' unique,
memo text default '',
adminuid int unsigned not null default 1,
parent int unsigned not null default 1,
primary key (gid)
)