drop table if exists `mini`;
create table `mini` (
    `id` bigint(20) unsigned auto_increment comment 'primary key',
    `name` varchar(32) not null default '' comment 'mini name',
    `type` tinyint(3) unsigned not null default 0 comment '1: common, 2: box',
    `category` varchar(16) not null default '' comment 'mini category',
    `pos` varchar(128) not null default '' comment 'resource pos',
    `mini_desc` varchar(128) not null default '' comment 'mini desc',
    `mode` varchar(128) not null default '' comment 'substitute mode',
    `company` varchar(32) not null default '' comment 'company name',
    `contact` varchar(8) not null default '' comment 'contact name',
    `mobile` varchar(16) not null default '' comment 'contact mobile',
    `wechat` varchar(64) not null default '' comment 'wechat id',
    `qq` varchar(12) not null default '' comment 'qq',
    `user_attrs` varchar(64) not null default '' comment 'user attributes',
    `total_user` int(11) unsigned not null default 0 comment 'total user',
    `dau` int(11) unsigned not null default 0 comment 'daily active user',
    `res_desc` varchar(128) not null default '' comment 'resource desc',
    `logo` varchar(128) not null default '' comment 'mini logo url',
    `status` tinyint(3) not null default 0 comment 'mini status, 0: offline, 1: online',
    primary key (`id`),
    unique key `name` (`name`),
    key `id_status` (`id`, `status`),
    key `dau` (`dau`, `status`),
    key `total_user` (`total_user`, `status`)
) Engine=InnoDB default charset=utf8;
