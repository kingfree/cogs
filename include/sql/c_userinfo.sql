create table userinfo
(
uid int unsigned auto_increment not null,
usr varchar(24) not null default '' unique,
pwdhash char(32) not null default '3b46d8d37a513c4a1f36bfa95aca77d3',
pwdtipques varchar(64) not null default '',
pwdtipanshash char(32) not null default '3b46d8d37a513c4a1f36bfa95aca77d3',
nickname varchar(16) not null default '',
readforce smallint not null default 0,
admin smallint not null default 0,
portrait smallint not null default 0,
grade int not null default 0,
memo text default '',
regtime int default 0,
realname varchar(16) not null default '',
gbelong int unsigned not null default 1,
submited int not null default 0,
email varchar(256) not null default '',
lastip varchar(16) not null default '',
primary key (uid)
)
