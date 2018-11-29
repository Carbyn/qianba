<?php
class NextController extends \Explorer\ControllerAbstract {

    private $ids = [
        'banner'    => [
            'title' => '顶部横图',
            'category' => '',
            'componentName' => 'banner',
            'ids' => [1420, 1346, 1254, 1255, 1155, 1012, 4, 8, 10, 14, 15]
        ],
        'recommend' => [
            'title' => '热门推荐',
            'category' => '',
            'componentName' => 'site',
            'ids' => [1420, 1346, 1254, 1155, 1012, 4, 7, 10]
        ],
        'matrix'    => [
            'title' => '柚友必备',
            'category' => '',
            'componentName' => 'site',
            'ids' => [998, 1, 2, 3]
        ],
        'latest'    => [
            'title' => '最新榜单',
            'category' => '',
            'componentName' => 'site',
            'ids' => [537, 649, 575, 77, 816, 474, 535, 138, 603, 863, 568, 433]
        ],
        'xiuxian'   => [
            'title' => '休闲游戏',
            'category' => 'xiuxian',
            'componentName' => 'site',
            'ids' => [1155]
        ],
        'juese'     => [
            'title' => '角色游戏',
            'category' => 'juese',
            'componentName' => 'site',
            'ids' => [1420, 1254, 1255, 1012, 7, 8, 9, 14]
        ],
        'qipai'     => [
            'title' => '棋牌游戏',
            'category' => 'qipai',
            'componentName' => 'site',
            'ids' => []
        ],
        'yangcheng' => [
            'title' => '养成游戏',
            'category' => 'yangcheng',
            'componentName' => 'site',
            'ids' => []
        ],
        'dongzuo'   => [
            'title' => '动作游戏',
            'category' => 'dongzuo',
            'componentName' => 'site',
            'ids' => []
        ],
        'jingsu'    => [
            'title' => '竞速游戏',
            'category' => 'jiangsu',
            'componentName' => 'site',
            'ids' => []
        ],
        'sheji'     => [
            'title' => '射击游戏',
            'category' => 'sheji',
            'componentName' => 'site',
            'ids' => [4, 10]
        ],
        'celue'     => [
            'title' => '策略游戏',
            'category' => 'celue',
            'componentName' => 'site',
            'ids' => []
        ],
        'yizhi'     => [
            'title' => '益智游戏',
            'category' => 'yizhi',
            'componentName' => 'site',
            'ids' => [15]
        ],
        'tiyu'      => [
            'title' => '体育游戏',
            'category' => 'tiyu',
            'componentName' => 'site',
            'ids' => []
        ],
        'yinyue'    => [
            'title' => '音乐游戏',
            'category' => 'yinyue',
            'componentName' => 'site',
            'ids' => []
        ],
        'ertong'    => [
            'title' => '儿童游戏',
            'category' => 'ertong',
            'componentName' => 'site',
            'ids' => []
        ],
    ];

    public function floorAction() {
        $gameModel = new GameModel();
        $ids = [];
        foreach($this->ids as $block) {
            $ids = array_merge($ids, $block['ids']);
        }
        $allgames = $gameModel->batchFetch($ids);

        $games = [];
        foreach($this->ids as $blockName => $block) {
            ${$blockName} = [];
            foreach($block['ids'] as $id) {
                if (isset($allgames[$id])) {
                    ${$blockName}[] = $this->adapt($allgames[$id]);
                }
            }
            if ($blockName == 'banner') {
                $first = ${$blockName}[0];
                ${$blockName} = array_slice(${$blockName}, 1);
                shuffle(${$blockName});
                ${$blockName} = array_slice(${$blockName}, 0, 3);
                array_unshift(${$blockName}, $first);
            }
            if ($block['category'] != '' && count(${$blockName}) < 20) {
                $categoryGames = $gameModel->fetchByCategory($block['category'], 1, 20);
                foreach($categoryGames as &$game) {
                    $game = $this->adapt($game);
                }
                ${$blockName} = array_slice(array_unique(array_merge(${$blockName}, $categoryGames), SORT_REGULAR), 0, 20);
            }
            $games[] = [
                'title' => $block['title'],
                'category' => $block['category'],
                'componentName' => $block['componentName'],
                'list' => ${$blockName},
            ];
        }

        $this->outputSuccess(compact('games'));
    }

    public function feedAction() {
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $page = max(1, $page);
        // todo
        $page = 4;
        $pagesize = 100;
        $gameModel = new GameModel();
        $games = $gameModel->fetchAll($page, $pagesize);
        foreach($games as &$game) {
            $game = $this->adapt($game);
        }
        $games = [
            'title' => '试玩专区',
            'componentName' => 'feed',
            'list' => array_values($games),
            'is_end' => count($games) < $pagesize,
        ];
        $this->outputSuccess(compact('games'));
    }

    private function adapt($game) {
        $task = [
			"id" => $game['id'],
			"name" => mb_strlen($game['name']) > 7 ? mb_substr($game['name'], 0, 7) : $game['name'],
			"type" => $game['appid'] ? 'navigate' : 'preview',
			"category" => "",
			"os" => "0",
			"parent_id" => "0",
			"subtasks" => "1",
			"task_desc" => "",
			"buttons" => "",
			"url" => $game['appid'],
			"apppath" => $game['apppath'],
			"code" => "",
			"reward" => 0,
			"app_reward" => 0,
			"icon" => $game['icon'],
			"images" => $game['banner'],
			"demos" => $game['qrcode'],
			"inventory" => "88888888",
			"status" => "0",
			"completed_num" => 0,
			"button_text" => "马上玩"
        ];
        return $task;
    }

}
