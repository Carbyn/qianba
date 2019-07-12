drop table if exists `words`;
create table `words` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `text` varchar(1024) not null default '' comment 'text',
    `source` varchar(64) not null default '' comment 'source',
    `md5` char(32) not null default '' comment 'workds md5 value',
    primary key (`id`),
    unique key `md5` (`md5`)
) ENGINE=InnoDB default CHARSET=utf8;
