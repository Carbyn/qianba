drop table if exists `help`;
create table `help` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `income_id` bigint(20) unsigned not null default 0 comment 'income id',
    `uid` bigint(20) unsigned not null default 0 comment 'help uid',
    `created_at` int(11) unsigned not null default 0 comment 'create time',
    primary key (`id`),
    unique key `income_uid` (`income_id`, `uid`)
) ENGINE=InnoDB default CHARSET=utf8;
