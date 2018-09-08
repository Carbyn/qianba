<?php
class NextController extends \Explorer\ControllerAbstract {

    public function indexAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $titles = [
            'banner' => 'banner',
            'recommend' => '热门推荐',
            'try' => '试玩专区',
        ];
        $duration = 60;
        $stat = 'from=xiaozhuqianba';

        $bannerIds = [75, 68, 40];
        $recommendIds = [52, 67, 51, 61, 35, 50, 59, 69, 64, 63, 48, 39];
        $tryIds = [31, 32, 33, 34, 36, 37, 38, 46, 47, 49, 60, 62, 65, 66, 77, 71, 72, 73, 74, 76];

        $taskModel = new TaskModel();
        $tasks = $taskModel->batchFetch(array_merge($bannerIds, $recommendIds, $tryIds));
        foreach($tasks as &$task) {
            $task['stat'] = $stat;
            if ($task['url']) {
                $task['type'] = 'navigate';
            } else {
                $task['type'] = 'preview';
            }
        }
        $mytaskModel = new MytaskModel();
        $mytasks = $mytaskModel->fetchTasks($this->uid, Constants::TYPE_TASK_MINI);

        $banner = $recommend = $try = [];
        foreach($bannerIds as $id) {
            if (isset($tasks[$id])) {
                $tasks[$id]['completed_num'] = 0;
                $banner[] = $tasks[$id];
            }
        }
        foreach($recommendIds as $id) {
            if (isset($tasks[$id])) {
                $tasks[$id]['completed_num'] = 0;
                $recommend[] = $tasks[$id];
            }
        }
        foreach($tryIds as $id) {
            if (isset($tasks[$id])) {
                $tasks[$id]['completed_num'] = 0;
                if ($tasks[$id]['reward'] == 0) {
                    $tasks[$id]['button_text'] = '马上玩';
                } else if (isset($mytasks[$id])) {
                    $tasks[$id]['completed_num'] = 1;
                    $tasks[$id]['button_text'] = '马上玩';
                } else {
                    $tasks[$id]['button_text'] = '试玩￥'.$tasks[$id]['reward'];
                }
                $try[] = $tasks[$id];
            }
        }

        shuffle($banner);

        $games = compact('banner', 'recommend', 'try');

        $this->outputSuccess(compact('titles', 'duration', 'games'));
    }

}
