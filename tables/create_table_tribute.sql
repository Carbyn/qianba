drop table if exists `tribute`;
create table `tribute` (
    `id` bigint(20) unsigned auto_increment comment 'primary key',
    `uid` bigint(20) unsigned not null default 0 comment 'user id',
    `type` tinyint(3) unsigned not null default 0 comment 'tudi=1 or tusun=2',
    `ouid` bigint(20) unsigned not null default 0 comment 'tudi or tusun id',
    `oname` varchar(32) not null default '' comment 'tudi or tusun name',
    `amount` bigint(11) unsigned not null default 0 comment 'money amount, unit: 0.01',
    `created_at` int(11) unsigned not null default 0 comment 'relationship create time',
    primary key (`id`),
    unique key `uid_ouid` (`uid`, `ouid`),
    key `uid_type` (`uid`, `type`),
    key `ouid_type` (`ouid`, `type`)
) Engine=InnoDB default charset=utf8;
