drop table if exists `history`;
create table `history` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `gameids` varchar(10240) not null default '' comment 'game ids, separated by ,',
    `updated_at` int(11) unsigned not null default 0 comment 'update time',
    primary key (`id`),
    key `uid` (`uid`)
) ENGINE=InnoDB default CHARSET=utf8;
