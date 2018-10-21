<?php
class TaskModel extends AbstractModel {

    const TABLE = 'task';
    const TTL_PROGRESS = 86400;

    private static $tasks = [
        Constants::TASK_NEW => [
            'id' => Constants::TASK_NEW,
            'type' => Constants::TYPE_TASK_ONCE,
            'name' => '新手任务',
            'task_desc' => '',
            'game_count' => 3,
            'duration' => 5,
            'reward' => 0.1,
        ],
        Constants::TASK_PIN => [
            'id' => Constants::TASK_PIN,
            'type' => Constants::TYPE_TASK_ONCE,
            'name' => '收藏小程序',
            'task_desc' => '',
            'game_count' => 0,
            'duration' => 0,
            'reward' => 0.1,
        ],
        Constants::TASK_DAILY => [
            'id' => Constants::TASK_DAILY,
            'type' => Constants::TYPE_TASK_DAILY,
            'name' => '日常任务',
            'task_desc' => '',
            'game_count' => 10,
            'duration' => 30,
            'reward' => 0.1,
        ],
        Constants::TASK_HELP => [
            'id' => Constants::TASK_HELP,
            'type' => Constants::TYPE_TASK_DAILY,
            'name' => '好友助力',
            'task_desc' => '',
            'game_count' => 1,
            'duration' => 1,
            'reward' => 0.1,
        ],
        Constants::TASK_INVITE => [
            'id' => Constants::TASK_INVITE,
            'type' => Constants::TYPE_TASK_FOREVER,
            'name' => '邀请好友',
            'task_desc' => '',
            'game_count' => 0,
            'duration' => 0,
            'reward' => 0.1,
        ],
    ];

    public function fetch($task_id) {
        return isset(self::$tasks[$task_id]) ? self::$tasks[$task_id] : false;
    }

    public function fetchAll() {
        return self::$tasks;
    }

    public function isCompleted($task_id, $game_count, $duration) {
        if ($game_count >= self::$tasks[$task_id]['game_count']
            && $duration >= self::$tasks[$task_id]['duration']) {
                return true;
        }
        return false;
    }

}
