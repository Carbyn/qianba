drop table if exists `income`;
create table `income` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `task_desc` varchar(64) not null default '' comment 'task desc',
    `income` bigint(20) unsigned not null default 0 comment 'unit, 0.01',
    `created_at` int(11) unsigned not null default 0 comment 'create time',
    primary key (`id`),
    key `uid` (`uid`)
) ENGINE=InnoDB default CHARSET=utf8;
