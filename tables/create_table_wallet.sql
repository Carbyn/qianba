drop table if exists `wallet`;
create table `wallet` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `balance` bigint(20) unsigned not null default 0 comment 'unit, 0.01',
    `income` bigint(20) unsigned not null default 0 comment 'unit, 0.01',
    `receipt` varchar(256) not null default '' comment 'receipt code',
    primary key (`id`),
    unique key `uid` (`uid`)
) ENGINE=InnoDB default CHARSET=utf8;
