create table settings
(
ssid int unsigned auto_increment not null,
name varchar(24) not null default '' unique,
value text default '',
primary key (ssid)
)