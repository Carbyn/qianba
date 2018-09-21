drop table if exists `game`;
create table `game` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `name` varchar(32) not null default '' comment 'game name',
    `category` varchar(32) not null default '' comment 'minigame category',
    `icon` varchar(1024) not null default '' comment 'minigame icon',
    `banner` varchar(1024) not null default '' comment 'minigame banner',
    `appid` varchar(1024) not null default '' comment 'minigame appid',
    `apppath` varchar(128) not null default '' comment 'minigame start path',
    `qrcode` varchar(1024) not null default '' comment 'minigame qrcode',
    `status` tinyint(3) unsigned not null default 0 comment 'game status',
    primary key (`id`),
    key `category` (`category`, `status`),
    unique key `name` (`name`)
) ENGINE=InnoDB default CHARSET=utf8;
