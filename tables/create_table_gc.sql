drop table if exists `gc`;
create table `gc` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `garbage` varchar(32) not null default '' comment 'garbage',
    `classification` tinyint(3) unsigned not null default 0 comment 'garbage classification',
    `count` bigint(20) unsigned not null default 0 comment 'query garbage count',
    primary key (`id`),
    unique key `garbage` (`garbage`)
) ENGINE=InnoDB default CHARSET=utf8;

