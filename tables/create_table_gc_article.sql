drop table if exists `gc_article`;
create table `gc_article` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `oid` varchar(32) not null default '' comment 'event original id',
    `source` varchar(32) not null default '' comment 'source name',
    `title` varchar(128) not null default '' comment 'event title',
    `imgs` varchar(10240) not null default '' comment 'thumbnails',
    `content` text not null default '' comment 'article content, json',
    `publisher` varchar(32) not null default '' comment 'publisher name',
    `publisher_avatar` varchar(1024) not null default '' comment 'publish avatar',
    `published_at` int(11) unsigned not null default 0 comment 'published time',
    primary key (`id`),
    unique key `source` (`source`, `oid`)
) ENGINE=InnoDB default CHARSET=utf8;


