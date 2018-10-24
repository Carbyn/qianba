<?php
class TaskController extends \Explorer\ControllerAbstract {

    public function todayAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $taskModel = new TaskModel();
        $allTasks = $taskModel->fetchAll();
        unset($allTasks[Constants::TASK_HELP]);

        $mytaskModel = new MytaskModel();

        $tasks = [];
        foreach($allTasks as $task) {
            switch($task['type']) {
            case Constants::TYPE_TASK_ONCE:
                if (!$mytaskModel->fetch($this->uid, $task['id'], 0)) {
                    $tasks[] = $task;
                }
                break;
            case Constants::TYPE_TASK_DAILY:
                if (!$mytaskModel->fetch($this->uid, $task['id'], date('Ymd'))) {
                    $tasks[] = $task;
                }
                break;
            case Constants::TYPE_TASK_FOREVER:
                $tasks[] = $task;
                break;
            default:
            }
        }

        $progress = $mytaskModel->fetchProgress($this->uid);

        return $this->outputSuccess(compact('tasks', 'progress'));
    }

    public function reportAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $gameid = (int)$this->getRequest()->getQuery('gameid');
        $duration = (int)$this->getRequest()->getQuery('duration');
        if (!$gameid) {
            return $this->outputError(Constants::ERR_TASK_GAME_NOT_EXIST, '游戏不存在');
        }
        $gameModel = new GameModel();
        if (!$gameModel->fetch($gameid)) {
            return $this->outputError(Constants::ERR_TASK_GAME_NOT_EXIST, '游戏不存在');
        }
        if ($duration < Constants::GAME_PLAY_VALID_DURATION) {
            return $this->outputError(Constants::ERR_TASK_DURATION_NOT_ENOUGH, '游戏时间过短');
        }
        $duration = intval($duration / 60);

        $historyModel = new HistoryModel();
        $historyModel->add($this->uid, $gameid);

        $mytaskModel = new MytaskModel();
        $progress = $mytaskModel->updateProgress($this->uid, $gameid, $duration);

        $taskModel = new TaskModel();
        $allTasks = $taskModel->fetchAll();

        $rewards = [];

        if ($this->maybeHelp($this->uid, $progress)) {
            $rewards[] = $allTasks[Constants::TASK_HELP];
        }
        if ($this->maybeNew($this->uid, $progress)) {
            $rewards[] = $allTasks[Constants::TASK_NEW];
        }
        if ($income_id = $this->maybeDaily($this->uid, $progress)) {
            $task = $allTasks[Constants::TASK_DAILY];
            $task['income_id'] = $income_id;
            $rewards[] = $task;
        }


        return $this->outputSuccess(compact('rewards'));
    }

    public function pinAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $mytaskModel = new MytaskModel();
        if ($mytaskModel->fetch($this->uid, Constants::TASK_PIN)) {
            return $this->outputError(Constants::ERR_TASK_PIN_ALREADY_DONE, '已经收藏过了');
        }

        $taskModel = new TaskModel();
        $walletModel = new WalletModel();
        $incomeModel = new IncomeModel();

        $task = $taskModel->fetch(Constants::TASK_PIN);
        $mytaskModel->create($this->uid, $task['id']);
        $walletModel->reward($this->uid, $task['reward']);
        $incomeModel->create($this->uid, $task['name'], $task['reward']);

        $rewards[] = $task;

        $this->outputSuccess(compact('rewards'));
    }

    private function maybeHelp($uid, $progress) {
        $helpModel = new HelpModel();
        $taskModel = new TaskModel();
        $mytaskModel = new MytaskModel();
        $walletModel = new WalletModel();
        $incomeModel = new IncomeModel();
        $helpModel = new HelpModel();

        if (($income_id = $helpModel->inHelping($uid))
            && $taskModel->isCompleted(Constants::TASK_HELP, $progress['game_count'], $progress['duration'])
            && !$mytaskModel->fetch($uid, Constants::TASK_HELP, date('Ymd'))) {

            $task = $taskModel->fetch(Constants::TASK_HELP);

            $helpModel->endHelp($uid);
            $records = $helpModel->fetchAll($income_id);
            if (count($records) >= Constants::HELP_MAX) {
                return false;
            }
            $mytaskModel->create($uid, $task['id']);
            $helpModel->create($income_id, $uid);
            if (count($records) + 1 == Constants::HELP_MAX) {
                $income = $incomeModel->fetch($income_id);
                $walletModel->reward($income->uid, $task['reward']);
                $incomeModel->create($income->uid, $task['name'], $task['reward']);
            }

            return true;
        }
        return false;
    }

    private function maybeNew($uid, $progress) {
        $helpModel = new HelpModel();
        $taskModel = new TaskModel();
        $mytaskModel = new MytaskModel();
        $walletModel = new WalletModel();
        $incomeModel = new IncomeModel();

        if ($taskModel->isCompleted(Constants::TASK_NEW, $progress['game_count'], $progress['duration'])
            && !$mytaskModel->fetch($this->uid, Constants::TASK_NEW)) {

            $task = $taskModel->fetch(Constants::TASK_NEW);
            $mytaskModel->create($uid, $task['id']);
            $walletModel->reward($uid, $task['reward']);
            $incomeModel->create($uid, $task['name'], $task['reward']);

            $tributeModel = new TributeModel();
            $masterUid = $tributeModel->fetchMaster($uid);
            if ($masterUid) {
                $task = $taskModel->fetch(Constants::TASK_INVITE);
                $walletModel->reward($masterUid, $task['reward']);
                $incomeModel->create($masterUid, $task['name'], $task['reward']);
            }

            return true;
        }
        return false;
    }

    private function maybeDaily($uid, $progress) {
        $taskModel = new TaskModel();
        $mytaskModel = new MytaskModel();
        $walletModel = new WalletModel();
        $incomeModel = new IncomeModel();

        if ($taskModel->isCompleted(Constants::TASK_DAILY, $progress['game_count'], $progress['duration'])
            && !$mytaskModel->fetch($this->uid, Constants::TASK_DAILY, date('Ymd'))) {

            $task = $taskModel->fetch(Constants::TASK_DAILY);
            $wheelModel = new WheelModel();
            if ($wheelModel->hasReward($this->uid, 'card')) {
                $reward = $task['reward'] * 2;
            }
            $mytaskModel->create($uid, $task['id']);
            $walletModel->reward($uid, $reward);
            $income_id = $incomeModel->create($uid, $task['name'], $reward);
            return $income_id;
        }
        return false;
    }

    private function donePin($uid) {
        $taskModel = new TaskModel();
        $mytaskModel = new MytaskModel();
        $walletModel = new WalletModel();
        $incomeModel = new IncomeModel();

        if ($taskModel->isCompleted(Constants::TASK_DAILY, $progress['game_count'], $progress['duration'])
            && !$mytaskModel->fetch($this->uid, Constants::TASK_DAILY, date('Ymd'))) {

            $task = $taskModel->fetch(Constants::TASK_DAILY);
            $mytaskModel->create($uid, $task['id']);
            $walletModel->reward($uid, $task['reward']);
            $incomeModel->create($uid, $task['name'], $task['reward']);
            return true;
        }
        return false;
    }

}
