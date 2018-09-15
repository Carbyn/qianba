<?php
class NextController extends \Explorer\ControllerAbstract {

    private $ids = [
        'banner'    => ['title' => '顶部横图', 'componentName' => 'banner', 'ids' => [81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91]],
        'recommend' => ['title' => '热门推荐', 'componentName' => 'site',   'ids' => [81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 52]],
        'matrix'    => ['title' => '柚友必备', 'componentName' => 'site',   'ids' => [78, 79, 80]],
        'latest'    => ['title' => '最新榜单', 'componentName' => 'site',   'ids' => [51, 52, 92, 93, 94, 50, 95, 96, 97, 98, 99, 100]],
        'xiuxian'   => ['title' => '休闲游戏', 'componentName' => 'site',   'ids' => [101, 102, 103, 104, 105, 106, 107, 108]],
        'juese'     => ['title' => '角色游戏', 'componentName' => 'site',   'ids' => [82, 83, 84, 85, 86, 88, 90, 91]],
        'qipai'     => ['title' => '棋牌游戏', 'componentName' => 'site',   'ids' => [109, 110, 111, 112, 113, 114, 115, 116]],
        'yangcheng' => ['title' => '养成游戏', 'componentName' => 'site',   'ids' => [117, 118, 119, 120, 121, 122, 123, 124]],
        'dongzuo'   => ['title' => '动作游戏', 'componentName' => 'site',   'ids' => [125, 126, 127, 128, 129, 130, 131, 132]],
        'jingsu'    => ['title' => '竞速游戏', 'componentName' => 'site',   'ids' => [133, 134, 135, 136, 137, 138, 139, 140]],
        'sheji'     => ['title' => '射击游戏', 'componentName' => 'site',   'ids' => [81, 87, 89, 141, 142, 143, 144, 145]],
        'celue'     => ['title' => '策略游戏', 'componentName' => 'site',   'ids' => [146, 147, 148, 149, 150, 151, 152, 153]],
        'yizhi'     => ['title' => '益智游戏', 'componentName' => 'site',   'ids' => [154, 155, 156, 157, 158, 159, 160, 161]],
    ];

    private $feedIds = [40, 75, 68, 67, 61, 35, 59, 69, 64, 63, 48, 37, 31, 32, 33, 34, 36, 38, 39, 46, 47, 49, 60, 62, 65, 66, 77, 71, 72, 73, 74, 76];

    public function floorAction() {
        $taskModel = new TaskModel();
        $ids = [];
        foreach($this->ids as $block) {
            $ids = array_merge($ids, $block['ids']);
        }
        $tasks = $taskModel->batchFetch($ids);

        foreach($tasks as &$task) {
            $this->format($task);
        }

        $games = [];
        foreach($this->ids as $blockName => $block) {
            ${$blockName} = [];
            foreach($block['ids'] as $id) {
                if (isset($tasks[$id])) {
                    ${$blockName}[] = $tasks[$id];
                }
            }
            if ($blockName == 'banner') {
                shuffle(${$blockName});
                ${$blockName} = array_slice(${$blockName}, 0, 4);
            }
            $games[] = [
                'title' => $block['title'],
                'componentName' => $block['componentName'],
                'list' => ${$blockName},
            ];
        }

        $this->outputSuccess(compact('games'));
    }

    public function feedAction() {
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $page = max(1, $page);
        $limit = 25;
        $offset = ($page - 1) * $limit;
        $feedIds = array_slice($this->feedIds, $offset, $limit);
        if (empty($feedIds)) {
            $tasks = [];
        } else {
            $taskModel = new TaskModel();
            $tasks = $taskModel->batchFetch($feedIds);
            foreach($tasks as &$task) {
                $this->format($task);
            }
            shuffle($tasks);
        }
        $games = [
            'title' => '试玩专区',
            'componentName' => 'feed',
            'list' => $tasks,
            'is_end' => count($tasks) < $limit,
        ];
        $this->outputSuccess(compact('games'));
    }

    private function format(&$task) {
        $task['completed_num'] = 0;
        $task['button_text'] = '马上玩';
        if ($task['url']) {
            $task['type'] = 'navigate';
        } else {
            $task['type'] = 'preview';
        }
    }

}
