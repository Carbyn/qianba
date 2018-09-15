<?php
class TaskController extends \Explorer\ControllerAbstract {

    public function todayAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $type = (int)$this->getRequest()->getQuery('type', Constants::TYPE_TASK_CPA);
        if ($type != Constants::TYPE_TASK_CPA && $type != Constants::TYPE_TASK_MINI) {
            return $this->outputError(Constants::ERR_TASK_TYPE_INVALID, '类型无效');
        }

        $taskModel = new TaskModel();
        $tasks = $taskModel->fetchTasks($type, $this->os);
        $mytaskModel = new MytaskModel();
        $mytasks = $mytaskModel->fetchTasks($this->uid, $type);

        $today = [];
        foreach($tasks as &$task) {
            if (isset($mytasks[$task['id']])) {
                $task['completed_num'] = $mytasks[$task['id']]['completed_num'];
            } else {
                $task['completed_num'] = 0;
            }
            if ($type == Constants::TYPE_TASK_CPA && $task['completed_num'] == $task['subtasks']) {
                continue;
            }
            if ($type == Constants::TYPE_TASK_CPA) {
                $task['type'] = 'detail';
                if ($task['completed_num'] == 0) {
                    $task['button_text'] = '去赚钱';
                } else if ($task['completed_num'] < $task['subtasks']) {
                    $task['button_text'] = $task['completed_num'].'/'.$task['subtasks'];
                } else {
                }
            } else {
                if ($task['url']) {
                    $task['type'] = 'navigate';
                } else {
                    $task['type'] = 'preview';
                }
                if ($task['reward'] == 0) {
                    $task['completed_num'] = 1;
                }
                $task['button_text'] = $task['subtasks'] == $task['completed_num'] ? '再玩一次' : '试玩赚钱￥'.$task['reward'];
            }
            $today[] = $task;
        }
        if ($type == Constants::TYPE_TASK_MINI && $this->os == Constants::OS_ANDROID) {
            array_shift($today);
        }
        $today = array_values($today);
        $duration = 60;
        $this->outputSuccess(compact('today', 'duration'));
    }

    public function historyAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $history = [];
        $mytaskModel = new MytaskModel();
        $mytasks = $mytaskModel->fetchCompletedTasks($this->uid);
        if (!empty($mytasks)) {
            $task_ids = array_keys($mytasks);
            $taskModel = new TaskModel();
            $history = $taskModel->batchFetch($task_ids);
        }
        $history = array_values($history);
        $this->outputSuccess(compact('history'));
    }

    public function detailAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $task_id = $this->getRequest()->getQuery('task_id');

        $taskModel = new TaskModel();
        $task = $taskModel->fetch($task_id);
        if (!$task) {
            return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
        }
        $subtasks = $taskModel->fetchSubtasks($task_id);
        if (!empty($subtasks)) {
            $task_ids = array_keys($subtasks);
            $mytaskModel = new MytaskModel();
            $mytask = $mytaskModel->fetchTask($this->uid, $task_id);
            $mysubtasks = $mytaskModel->fetchSubtasks($this->uid, $task_ids);
            foreach($mysubtasks as $task_id => $mysubtask) {
                $subtasks[$task_id]['mytask'] = $mysubtask;
            }
            $subtasks = array_values($subtasks);
        } else {
            $mytask = $subtasks = [];
        }

        if ($this->os == Constants::OS_IOS) {
            $guide = 'http://qianba.oss-cn-huhehaote.aliyuncs.com/mp4/ios_guide.mp4';
        } else {
            $guide = 'https://qianba.oss-cn-huhehaote.aliyuncs.com/mp4/new_guide.mp4';
        }

        $this->outputSuccess(compact('task', 'mytask', 'subtasks', 'guide'));
    }

    public function completeAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $task_id = $this->getRequest()->getPost('task_id');
        $screenshots = $this->getRequest()->getPost('screenshots');
        $taskModel = new TaskModel();
        $task = $taskModel->fetch($task_id);
        if (!$task) {
            return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
        }
        if ($task->type == Constants::TYPE_TASK_CPA) {
            if ($task->parent_id == 0) {
                return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
            }
            $parentTask = $taskModel->fetch($task->parent_id);
            if ($parentTask->inventory <= 0) {
                return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务已下线');
            }
            if (!$screenshots) {
                return $this->outputError(Constants::ERR_TASK_SCREENSHOTS_LOST, '请提交截图');
            }
        } else {
            if ($task->reward == 0) {
                return $this->outputSuccess();
            }
        }

        $mytaskModel = new MytaskModel();
        switch($task->type) {
        case Constants::TYPE_TASK_CPA:
            $subtask = $mytaskModel->fetchTask($this->uid, $task_id);
            if ($subtask) {
                switch($subtask->status) {
                case Constants::STATUS_MYTASK_IN_REVIEW:
                case Constants::STATUS_MYTASK_APPROVED:
                    return $this->outputError(Constants::ERR_TASK_IN_REVIEW, '任务已提交');
                case Constants::STATUS_MYTASK_UNAPPROVED:
                    $mytaskModel->recompleteSubtask($this->uid, $task_id, $screenshots);
                }
            } else {
                $mytaskModel->completeSubtask($this->uid, $task_id, $screenshots);
                $taskModel->decrInventory($task->parent_id);
            }
            return $this->outputSuccess();
        case Constants::TYPE_TASK_MINI:
            $mytask = $mytaskModel->fetchTask($this->uid, $task_id, date('Ymd'));
            if ($mytask) {
                return $this->outputError(Constants::ERR_TASK_IN_REVIEW, '任务已完成');
            }
            if (!$mytaskModel->completeTask($this->uid, $task_id)) {
                return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '任务奖励失败');
            }
            $taskModel->decrInventory($task_id);
            return $this->rewardTask($this->uid, $task->reward, $task->name);
        }
    }

    public function createTaskAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $name = $this->getRequest()->getPost('name');
        $type = $this->getRequest()->getPost('type');
        $os = $this->getRequest()->getPost('os');
        $task_desc = $this->getRequest()->getPost('task_desc');
        $url = $this->getRequest()->getPost('url');
        $apppath = $this->getRequest()->getPost('apppath');
        $reward = $this->getRequest()->getPost('reward');
        $icon = $this->getRequest()->getPost('icon');
        $images = $this->getRequest()->getPost('images');
        $demos = $this->getRequest()->getPost('demos');
        $inventory = (int)$this->getRequest()->getPost('inventory');

        if (!$name || !is_numeric($type) || !$os || !is_numeric($reward) || !$icon) {
            return $this->outputError(Constants::ERR_TASK_CREATE_INFO_INVALID, '任务信息不全');
        }
        $taskModel = new TaskModel();
        $id = $taskModel->createTask($name, $type, $os, $task_desc, $url, $apppath, $reward, $icon, $images, $demos, $inventory);
        if (!$id) {
            return $this->outputError(Constants::ERR_TASK_CREATE_FAILED, '任务创建失败');
        }
        $this->outputSuccess(compact('id'));
    }

    public function createSubtaskAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $name = $this->getRequest()->getPost('name');
        $type = $this->getRequest()->getPost('type');
        $parent_id = $this->getRequest()->getPost('parent_id');
        $task_desc = $this->getRequest()->getPost('task_desc');
        $buttons = $this->getRequest()->getPost('buttons');
        $url = $this->getRequest()->getPost('url');
        $code = $this->getRequest()->getPost('code');
        $reward = $this->getRequest()->getPost('reward');
        $app_reward = $this->getRequest()->getPost('app_reward');
        $images = $this->getRequest()->getPost('images');
        $demos = $this->getRequest()->getPost('demos');

        if (!$parent_id || !$task_desc || !$reward || !$images || !$demos) {
            return $this->outputError(Constants::ERR_TASK_CREATE_INFO_INVALID, '任务信息不全');
        }
        $taskModel = new TaskModel();
        $task = $taskModel->fetch($parent_id);
        if (!$task) {
            return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
        }
        $id = $taskModel->createSubtask($name, $type, $parent_id, $task_desc, $buttons, $url, $code, $reward, $app_reward, $images, $demos);
        if (!$id) {
            return $this->outputError(Constants::ERR_TASK_CREATE_FAILED, '任务创建失败');
        }
        $this->outputSuccess(compact('id'));
    }

    public function updateAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $task_id = $this->getRequest()->getQuery('id');
        $online = $this->getRequest()->getQuery('online');
        $taskModel = new TaskModel();
        $task = $taskModel->fetch($task_id);
        if (!$task) {
            return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
        }
        if (!$taskModel->update($task_id, $online)) {
            return $this->outputError(Constants::ERR_TASK_UPDATE_FAILED, '更新任务失败');
        }
        $this->outputSuccess();
    }

    public function reviewAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $id = $this->getRequest()->getQuery('id');
        $approved = $this->getRequest()->getQuery('approved');
        $mytaskModel = new MytaskModel();
        $mytask = $mytaskModel->fetch($id);
        if (!$mytask) {
            return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
        }
        if ($mytask->status != Constants::STATUS_MYTASK_IN_REVIEW) {
            return $this->outputError(Constants::ERR_TASK_ALREADY_REVIEWED, '任务已被审核');
        }
        if (!$mytaskModel->review($id, $approved)) {
            return $this->outputError(Constants::ERR_TASK_APPROVED_FAILED, '通过任务失败');
        }
        if (!$approved) {
            return $this->outputSuccess();
        }

        $taskModel = new TaskModel();
        $subtask = $taskModel->fetch($mytask->task_id);
        $task = $taskModel->fetch($subtask->parent_id);
        if (!$mytaskModel->incrTask($mytask->uid, $task->id, $task->subtasks)) {
            return $this->outputError(Constants::ERR_TASK_INCR_TASK_FAILED, '更新父任务失败');
        }

        return $this->rewardTask($mytask->uid, $subtask->reward, $task->name.$subtask->name);
    }

    private function rewardTask($uid, $reward, $incomeDesc) {
        $walletModel = new WalletModel();
        if (!$walletModel->reward($uid, $reward)) {
            return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '任务奖励失败');
        }

        $incomeModel = new IncomeModel();
        if (!$incomeModel->create($uid, $incomeDesc, $reward)) {
            return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '任务添加收入记录失败');
        }

        $tributeModel = new TributeModel();
        $mUid = $tributeModel->fetchMaster($uid);
        if ($mUid) {
            $mReward = $reward * Constants::PERCENT_TUDI;
            if (!$walletModel->reward($mUid, $mReward)) {
                return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '奖励师父失败');
            }
            if (!$incomeModel->create($mUid, '徒弟进贡', $mReward)) {
                return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '师父添加收入记录失败');
            }
            if (!$tributeModel->incrAmount($mUid, $uid, $mReward)) {
                return $this->outputError(Constants::ERR_TASK_TRIBUTE_INCR_FAILED, '进贡师父记录更新失败');
            }
            $mmUid = $tributeModel->fetchMaster($mUid);
            if ($mmUid) {
                $mmReward = $reward * Constants::PERCENT_TUSUN;
                if (!$walletModel->reward($mmUid, $mmReward)) {
                    return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '奖励师祖失败');
                }
                if (!$incomeModel->create($mmUid, '徒孙进贡', $mmReward)) {
                    return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '师祖添加收入记录失败');
                }
                if (!$tributeModel->fetch($mmUid, $uid)) {
                    $userModel = new UserModel();
                    $user = $userModel->fetch($uid);
                    if (!$tributeModel->bind($mmUid, Constants::TYPE_TRIBUTE_TUSUN, $uid, $user->name, $mmReward)) {
                        return $this->outputError(Constants::ERR_TASK_TRIBUTE_INCR_FAILED, '进贡师祖记录创建失败');
                    }
                    $userModel->incrTusun($mmUid);
                } else {
                    if (!$tributeModel->incrAmount($mmUid, $uid, $mmReward)) {
                        return $this->outputError(Constants::ERR_TASK_TRIBUTE_INCR_FAILED, '进贡师祖记录更新失败');
                    }
                }
            }
        }

        $this->outputSuccess(['button_text' => '马上玩', 'completed_num' => 1]);
    }

}
