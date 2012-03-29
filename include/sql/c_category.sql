create table category
(
caid int unsigned auto_increment not null,
cname varchar(24) not null default '' unique,
memo text default '',
primary key (caid)
)