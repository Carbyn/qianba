drop table if exists `task`;
create table `task` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `name` varchar(32) not null default '' comment 'task name',
    `type` tinyint(3) unsigned not null default 0 comment 'task type',
    `task_desc` varchar(1024) not null default '' comment 'task desc',
    primary key `id` (`id`)
) ENGINE=InnoDB default CHARSET=utf8;
