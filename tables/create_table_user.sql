drop table if exists `user`;
create table user (
    `id` bigint(20) unsigned auto_increment comment 'primary key',
    `openid` varchar(128) not null default '' comment 'wx openid',
    `name` varchar(32) not null default '' comment 'user name',
    `avatar` varchar(256) not null default '' comment 'user avatar',
    `code` int(11) unsigned not null default 0 comment 'my invitation code',
    `tudi_num` int(11) unsigned not null default 0 comment 'tudi num',
    `tusun_num` int(11) unsigned not null default 0 comment 'tusun num',
    `register_time` int(11) unsigned not null default 0 comment 'user register time',
    primary key (`id`),
    unique key uniq_openid(`openid`),
    unique key uniq_code(`code`)
) Engine=InnoDB default charset=utf8;
