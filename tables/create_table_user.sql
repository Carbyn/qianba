create table user (
    `id` bigint(20) unsigned auto_increment comment 'primary key',
    `openid` varchar(128) not null default '' comment 'wx openid',
    `name` varchar(32) not null default '' comment 'user name',
    `avatar` varchar(256) not null default '' comment 'user avatar',
    `register_time` int(11) unsigned not null default 0 comment 'user register time',
    primary key (`id`),
    unique key uniq_openid(`openid`)
) Engine=InnoDB default charset=utf8;
