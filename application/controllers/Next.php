<?php
class NextController extends \Explorer\ControllerAbstract {

    private $stat = 'from=youziyouxihezi';

    private $bannerIds = [81, 82, 75, 40];
    private $recommendIds = [83, 84, 85, 86, 87, 88, 52, 67, 51, 61, 35, 50];
    private $matrixIds = [78, 79, 80];
    private $feedIds = [59, 69, 64, 63, 48, 37, 31, 32, 33, 34, 36, 38, 39, 46, 47, 49, 60, 62, 65, 66, 77, 71, 72, 73, 74, 76];

    public function floorAction() {
        $taskModel = new TaskModel();
        $tasks = $taskModel->batchFetch(array_merge($this->bannerIds, $this->recommendIds, $this->matrixIds));
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
        foreach($this->bannerIds as $id) {
            if (isset($tasks[$id])) {
                $banner[] = $tasks[$id];
            }
        }
        foreach($this->recommendIds as $id) {
            if (isset($tasks[$id])) {
                $recommend[] = $tasks[$id];
            }
        }
        foreach($this->matrixIds as $id) {
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
