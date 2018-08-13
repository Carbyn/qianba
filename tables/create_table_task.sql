drop table if exists `task`;
create table `task` (
    `id` bigint(20) unsigned not null default 0 comment 'auto incr id',
    `parent_id` bigint(20) unsigned not null default 0 comment 'parent task id',
    `subtasks` tinyint(3) unsigned not null default 0 comment 'subtasks num',
    `task_desc` varchar(128) not null default '' comment 'task desc',
    `reward` bigint(20) unsigned not null default 0 comment 'task reward, unit 0.001',
    `images` varchar(1024) not null default '' comment 'task desc images, seperated by |',
    `demos` varchar(1024) not null default '' comment 'task upload image demos, seperated by |',
    `status` tinyint(3) unsigned not null default 0 comment 'task status',
    primary key (`id`),
    key `parent` (`parent_id`)
) ENGINE=InnoDB default CHARSET=utf8;
