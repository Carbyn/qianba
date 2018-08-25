<?php
class TaskController extends \Explorer\ControllerAbstract {

    public function todayAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $taskModel = new TaskModel();
        $tasks = $taskModel->fetchTasks(true, $this->os);
        $mytaskModel = new MytaskModel();
        $mytasks = $mytaskModel->fetchTasks($this->uid);

        $today = [];
        foreach($tasks as $task) {
            if (isset($mytasks[$task['id']])) {
                if ($mytasks[$task['id']]['status'] == Constants::STATUS_MYTASK_APPROVED) {
                    continue;
                } else {
                    $task['completed_num'] = $mytasks[$task['id']]['completed_num'];
                }
            } else {
                $task['completed_num'] = 0;
            }
            $today[] = $task;
        }
        $today = array_values($today);
        $this->outputSuccess(compact('today'));
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
        if (!$task || $task->parent_id == 0) {
            return $this->outputError(Constants::ERR_TASK_NOT_EXISTS, '任务不存在');
        }
        $mytaskModel = new MytaskModel();
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
        }
        $this->outputSuccess();
    }

    public function createTaskAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $name = $this->getRequest()->getPost('name');
        $os = $this->getRequest()->getPost('os');
        $reward = $this->getRequest()->getPost('reward');
        $images = $this->getRequest()->getPost('images');

        if (!$name || !$os || !$reward || !$images) {
            return $this->outputError(Constants::ERR_TASK_CREATE_INFO_INVALID, '任务信息不全');
        }
        $taskModel = new TaskModel();
        $id = $taskModel->createTask($name, $os, $reward, $images);
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
        $parent_id = $this->getRequest()->getPost('parent_id');
        $task_desc = $this->getRequest()->getPost('task_desc');
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
        $id = $taskModel->createSubtask($name, $parent_id, $task_desc, $url, $code, $reward, $app_reward, $images, $demos);
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

        $walletModel = new WalletModel();
        if (!$walletModel->reward($mytask->uid, $subtask->reward)) {
            return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '任务奖励失败');
        }

        $incomeModel = new IncomeModel();
        if (!$incomeModel->create($mytask->uid, $task->name.$subtask->name, $subtask->reward)) {
            return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '任务添加收入记录失败');
        }

        $tributeModel = new TributeModel();
        $mUid = $tributeModel->fetchMaster($mytask->uid);
        if ($mUid) {
            $mReward = $subtask->reward * Constants::PERCENT_TUDI;
            if (!$walletModel->reward($mUid, $mReward)) {
                return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '奖励师父失败');
            }
            if (!$incomeModel->create($mUid, '徒弟进贡', $mReward)) {
                return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '师父添加收入记录失败');
            }
            if (!$tributeModel->incrAmount($mUid, $mytask->uid, $mReward)) {
                return $this->outputError(Constants::ERR_TASK_TRIBUTE_INCR_FAILED, '进贡师父记录更新失败');
            }
            $mmUid = $tributeModel->fetchMaster($mUid);
            if ($mmUid) {
                $mmReward = $subtask->reward * Constants::PERCENT_TUSUN;
                if (!$walletModel->reward($mmUid, $mmReward)) {
                    return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '奖励师祖失败');
                }
                if (!$incomeModel->create($mmUid, '徒孙进贡', $mmReward)) {
                    return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '师祖添加收入记录失败');
                }
                if (!$tributeModel->fetch($mmUid, $mytask->uid)) {
                    $userModel = new UserModel();
                    $user = $userModel->fetch($mytask->uid);
                    if (!$tributeModel->bind($mmUid, Constants::TYPE_TRIBUTE_TUSUN, $mytask->uid, $user->name, $mmReward)) {
                        return $this->outputError(Constants::ERR_TASK_TRIBUTE_INCR_FAILED, '进贡师祖记录创建失败');
                    }
                    $userModel->incrTusun($mmUid);
                } else {
                    if (!$tributeModel->incrAmount($mmUid, $mytask->uid, $mmReward)) {
                        return $this->outputError(Constants::ERR_TASK_TRIBUTE_INCR_FAILED, '进贡师祖记录更新失败');
                    }
                }
            }
        }

        $this->outputSuccess();
    }

}
