create table `wallet` (
    `id` bigint(20) unsigned not null default 0 comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `balance` bigint(20) unsigned not null default 0 comment 'unit, 0.001',
    `income` bigint(20) unsigned not null default 0 comment 'unit, 0.001',
    `receipt` varchar(256) not null default '' comment 'receipt code',
    primary key (`id`),
    unique key `uid` (`uid`)
) ENGINE=InnoDB default CHARSET=utf8;
