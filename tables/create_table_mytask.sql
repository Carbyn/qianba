drop table if exists `mytask`;
create table `mytask` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `task_id` bigint(20) unsigned not null default 0 comment 'task id',
    `is_subtask` tinyint(3) unsigned not null default 0 comment 'is subtask',
    `completed_num` tinyint(3) unsigned not null default 0 comment 'progress',
    `screenshots` varchar(1024) not null default '' comment 'task upload screenshots, seperated by |',
    `status` tinyint(3) unsigned not null default 0 comment 'user task status',
    `created_at` int(11) unsigned not null default 0 comment 'create time',
    primary key `id` (`id`),
    unique key `uid_task` (`uid`, `task_id`),
    key `uid_is_subtask` (`uid`, `is_subtask`)
) ENGINE=InnoDB default CHARSET=utf8;
