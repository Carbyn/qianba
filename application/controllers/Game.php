<?php
class GameController extends \Explorer\ControllerAbstract {

    const BLOCK_SIZE = 20;

    private $ids = [
        'banner'    => [
            'title' => '顶部横图',
            'category' => '',
            'componentName' => 'banner',
            'ids' => [1155, 1012, 4, 5, 8, 10, 12, 14, 15]
        ],
        'recommend' => [
            'title' => '热门推荐',
            'category' => '',
            'componentName' => 'site',
            'ids' => [1155, 1012, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15]
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
            'ids' => [1012, 5, 6, 7, 8, 9, 11, 13, 14]
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
            'ids' => [4, 10, 12]
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
                    ${$blockName}[] = $allgames[$id];
                }
            }
            if ($blockName == 'banner') {
                $first = ${$blockName}[0];
                ${$blockName} = array_slice(${$blockName}, 1);
                shuffle(${$blockName});
                ${$blockName} = array_slice(${$blockName}, 0, 3);
                array_unshift(${$blockName}, $first);
            }
            if ($block['category'] != '' && count(${$blockName}) < self::BLOCK_SIZE) {
                $categoryGames = $gameModel->fetchByCategory($block['category'], 1, self::BLOCK_SIZE);
                ${$blockName} = array_slice(array_unique(array_merge(${$blockName}, $categoryGames), SORT_REGULAR), 0, self::BLOCK_SIZE);
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

    public function historyAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $historyModel = new HistoryModel();
        $history = $historyModel->fetch($this->uid);

        $games = [];
        if ($history) {
            $gameModel = new GameModel();
            $games = $gameModel->batchFetch(explode(',', $history->gameids));
            $games = array_values($games);
        }

        $this->outputSuccess(compact('games'));
    }

    public function categoryAction() {
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $category = $this->getRequest()->getQuery('category');
        if (!$category) {
            return $this->outputError(Constants::ERR_GAME_PARAM_INVALID, '分类无效');
        }
        $pagesize = 40;
        $gameModel = new GameModel();
        $games = $gameModel->fetchByCategory($category, $page, $pagesize);
        $games = array_values($games);
        $is_end = count($games) < $pagesize;
        $this->outputSuccess(compact('games', 'is_end'));
    }

    public function createAction() {
        $gameModel = new GameModel();
        $id = $gameModel->create($this->getRequest()->getPost());
        $this->outputSuccess(compact('id'));
    }

    public function updateAction() {
        $id = $this->getRequest()->getQuery('id');
        $online = $this->getRequest()->getQuery('online');
        $gameModel = new GameModel();
        $game = $gameModel->fetch($id);
        if (!$game) {
            return $this->outputError(Constants::ERR_GAME_NOT_EXISTS, '小游戏不存在');
        }
        $gameModel->update($id, $online);
        $this->outputSuccess();
    }

}
