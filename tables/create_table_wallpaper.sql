drop table if exists `wallpaper`;
create table `wallpaper` (
    `id` bigint(20) unsigned auto_increment comment 'primary key',
    `oid` varchar(16) not null default '' comment 'original id',
    `full` varchar(1024) not null default '' comment 'full image url',
    `regular` varchar(1024) not null default '' comment 'regular image url',
    `small` varchar(1024) not null default '' comment 'small image url',
    `name` varchar(64) not null default '' comment 'author name',
    `source` varchar(1024) not null default '' comment 'source html url',
    primary key (`id`),
    unique key `oid` (`oid`)
) Engine=InnoDB default charset=utf8;

