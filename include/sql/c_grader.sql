create table grader
(
grid int unsigned auto_increment not null,
address text not null default '',
enabled tinyint not null default 1,
priority int not null default 1,
memo text default '',
primary key (grid)
)