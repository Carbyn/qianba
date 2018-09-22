drop table if exists `event`;
create table `event` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `oid` varchar(32) not null default '' comment 'event original id',
    `source` varchar(32) not null default '' comment 'source name',
    `tag` char(32) not null default '' comment 'event tag',
    `title` varchar(128) not null default '' comment 'event title',
    `description` varchar(10240) not null default '' comment 'event description',
    `image` varchar(1024) not null default '' comment 'event image',
    `published_at` int(11) unsigned not null default 0 comment 'published time',
    primary key (`id`),
    unique key `source` (`source`, `oid`),
    key `tag` (`tag`, `id`)
) ENGINE=InnoDB default CHARSET=utf8;

