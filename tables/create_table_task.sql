drop table if exists `task`;
create table `task` (
    `id` bigint(20) unsigned auto_increment comment 'auto incr id',
    `name` varchar(32) not null default '' comment 'task name',
    `type` tinyint(3) unsigned not null default 0 comment 'task type, 1:cpa,2:minigame',
    `os` tinyint(3) unsigned not null default 0 comment '0: android, 1: ios',
    `parent_id` bigint(20) unsigned not null default 0 comment 'parent task id',
    `subtasks` tinyint(3) unsigned not null default 0 comment 'subtasks num',
    `task_desc` varchar(128) not null default '' comment 'task desc',
    `buttons` varchar(10240) not null default '' comment 'buttons json',
    `url` varchar(1024) not null default '' comment 'task download or detail url',
    `apppath` varchar(128) not null default '' comment 'minigame start path',
    `code` varchar(32) not null default '' comment 'task official invitation code',
    `reward` bigint(20) unsigned not null default 0 comment 'task reward, unit 0.01',
    `app_reward` bigint(20) unsigned not null default 0 comment 'task app reward, unit 0.01',
    `images` varchar(1024) not null default '' comment 'task desc images, seperated by |',
    `demos` varchar(1024) not null default '' comment 'task upload image demos, seperated by |',
    `inventory` int(11) unsigned not null default 0 comment 'task inventory',
    `status` tinyint(3) unsigned not null default 0 comment 'task status',
    primary key (`id`),
    key `parent` (`parent_id`)
) ENGINE=InnoDB default CHARSET=utf8;
