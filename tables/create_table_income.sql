create table `income` (
    `id` bigint(20) unsigned not null default 0 comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `task_id` bigint(20) unsigned not null default 0 comment 'task id',
    `task_desc` varchar(64) not null default '' comment 'task desc',
    `income` bigint(20) unsigned not null default 0 comment 'unit, 0.001',
    `created_at` int(11) unsigned not null default 0 comment 'create time',
    primary key (`id`),
    unique key `uid_task` (`uid`, `task_id`)
) ENGINE=InnoDB default CHARSET=utf8;
