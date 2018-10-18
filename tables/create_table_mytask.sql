drop table if exists `mytask`;
create table `mytask` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `date` int(11) unsigned not null default 0 comment 'date 20180827',
    `task_id` bigint(20) unsigned not null default 0 comment 'task id',
    `created_at` int(11) unsigned not null default 0 comment 'create time',
    primary key `id` (`id`),
    unique key `uniq` (`uid`, `task_id`, `date`)
) ENGINE=InnoDB default CHARSET=utf8;
