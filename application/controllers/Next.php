<?php
class NextController extends \Explorer\ControllerAbstract {

    private $stat = 'from=youziyouxihezi';

    public function indexAction() {
        $matrix = [
            [
                'logo' => 'https://qianba.1024.pm/static/youzi.png',
                'url' => 'wx7104ccdc4d907073',
                'title' => '挑壁纸',
            ],
            [
                'logo' => 'https://qianba.1024.pm/static/youzi.png',
                'url' => 'wx618f3fe1bedd112e',
                'title' => '个税计算',
            ],
            [
                'logo' => 'https://qianba.1024.pm/static/youzi.png',
                'url' => 'wx337881826fe875fc',
                'title' => '快递助手',
            ],
        ];

        $titles = [
            'banner' => 'banner',
            'recommend' => '热门推荐',
            'partner' => '柚友必备',
            'try' => '试玩专区',
        ];
        $duration = 60;
        $stat = 'from=youziyouxihezi';

        $bannerIds = [75, 68, 40];
        $recommendIds = [52, 67, 51, 61, 35, 50, 59, 69, 64, 63, 48, 39];
        $tryIds = [31, 32, 33, 34, 36, 37, 38, 46, 47, 49, 60, 62, 65, 66, 77, 71, 72, 73, 74, 76];

        $taskModel = new TaskModel();
        $tasks = $taskModel->batchFetch(array_merge($bannerIds, $recommendIds, $tryIds));
        foreach($tasks as &$task) {
            $task['stat'] = $stat;
            $task['completed_num'] = 0;
            $task['button_text'] = '马上玩';
            if ($task['url']) {
                $task['type'] = 'navigate';
            } else {
                $task['type'] = 'preview';
            }
        }

        $banner = $recommend = $try = [];
        foreach($bannerIds as $id) {
            if (isset($tasks[$id])) {
                $banner[] = $tasks[$id];
            }
        }
        foreach($recommendIds as $id) {
            if (isset($tasks[$id])) {
                $recommend[] = $tasks[$id];
            }
        }
        foreach($tryIds as $id) {
            if (isset($tasks[$id])) {
                $try[] = $tasks[$id];
            }
        }

        shuffle($banner);
        shuffle($recommend);
        shuffle($try);

        $games = compact('banner', 'recommend', 'matrix', 'try');

        $this->outputSuccess(compact('titles', 'duration', 'games'));
    }

    public function floorAction() {
        $bannerIds = [81, 75, 68, 40];
        $recommendIds = [82, 52, 67, 51, 61, 35, 50, 59, 69, 64, 63, 48, 39];
        $matrixIds = [78, 79, 80];

        $taskModel = new TaskModel();
        $tasks = $taskModel->batchFetch(array_merge($bannerIds, $recommendIds, $matrixIds));
        foreach($tasks as &$task) {
            $task['stat'] = $this->stat;
            $task['completed_num'] = 0;
            $task['button_text'] = '马上玩';
            if ($task['url']) {
                $task['type'] = 'navigate';
            } else {
                $task['type'] = 'preview';
            }
        }

        $banner = $recommend = $try = [];
        foreach($bannerIds as $id) {
            if (isset($tasks[$id])) {
                $banner[] = $tasks[$id];
            }
        }
        foreach($recommendIds as $id) {
            if (isset($tasks[$id])) {
                $recommend[] = $tasks[$id];
            }
        }
        foreach($matrixIds as $id) {
            if (isset($tasks[$id])) {
                $matrix[] = $tasks[$id];
            }
        }

        $banner;
        $recommend;
        $games = [
            [
                'title' => 'banner',
                'componentName' => 'banner',
                'list' => $banner,
            ],
            [
                'title' => '热门推荐',
                'componentName' => 'site',
                'list' => $recommend,
            ],
            [
                'title' => '柚友必备',
                'componentName' => 'site',
                'list' => $matrix,
            ],
        ];
        $this->outputSuccess(compact('games'));
    }

    public function feedAction() {
        $feedIds = [31, 32, 33, 34, 36, 37, 38, 46, 47, 49, 60, 62, 65, 66, 77, 71, 72, 73, 74, 76];

        $page = (int)$this->getRequest()->getQuery('page', 1);
        $page = max(1, $page);
        $limit = 25;
        $offset = ($page - 1) * $limit;
        $feedIds = array_slice($feedIds, $offset, $limit);
        if (empty($feedIds)) {
            $tasks = [];
        } else {
            $taskModel = new TaskModel();
            $tasks = $taskModel->batchFetch($feedIds);
            foreach($tasks as &$task) {
                $task['stat'] = $this->stat;
                $task['completed_num'] = 0;
                $task['button_text'] = '马上玩';
                if ($task['url']) {
                    $task['type'] = 'navigate';
                } else {
                    $task['type'] = 'preview';
                }
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

}
