<?php
class TaskController extends \Explorer\ControllerAbstract {

    public function todayAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $taskModel = new TaskModel();
        $tasks = $taskModel->fetchTasks();
        $mytaskModel = new MytaskModel();
        $mytasks = $mytaskModel->fetchTasks($this->uid);

        $today = [];
        foreach($tasks as $task) {
            $task = (array)$task;
            if (isset($mytasks[$task['id']])) {
                if ($mytasks[$task['id']]->status == Constants::STATUS_MYTASK_APPROVED) {
                    continue;
                } else {
                    $task['completed_num'] = $mytasks[$task['id']]->completed_num;
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

        $taskModel = new TaskModel();
        $tasks = $taskModel->fetchTasks();
        $mytaskModel = new MytaskModel();
        $mytasks = $mytaskModel->fetchTasks($this->uid);

        $history = [];
        foreach($tasks as $task) {
            $task = (array)$task;
            if (isset($mytasks[$task['id']]) &&
                $mytasks[$task['id']]->status == Constants::STATUS_MYTASK_APPROVED) {
                $history[] = $task;
            }
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
        $task_ids = array_keys($subtasks);

        $mytaskModel = new MytaskModel();
        $mytask = $mytaskModel->fetchTask($this->uid, $task_id);
        $mysubtasks = $mytaskModel->fetchSubtasks($this->uid, $task_ids);
        foreach($mysubtasks as $task_id => $mysubtask) {
            $subtasks[$task_id]['mytask'] = $mysubtask;
        }
        $subtasks = array_values($subtasks);

        $this->outputSuccess(compact('task', 'mytask', 'subtasks'));
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
        $this->mytaskModel->completeSubtask($this->uid, $task_id, $screenshots);
        $this->outputSuccess();
    }

    public function approveAction() {
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
        if (!$mytaskModel->approve($id, $approved)) {
            return $this->outputError(Constants::ERR_TASK_APPROVED_FAILED, '通过任务失败');
        }
        if (!$approved) {
            return $this->outputSuccess();
        }

        $taskModel = new TaskModel();
        $subtask = $taskModel->fetch($mytask->task_id);
        $task = $taskModel->fetch($subtask->parent_id);
        if (!$mytaskModel->incrTask($this->uid, $task->id, $task->subtasks)) {
            return $this->outputError(Constants::ERR_TASK_INCR_TASK_FAILED, '更新父任务失败');
        }

        $walletModel = new WalletModel();
        if (!$walletModel->reward($mytask->uid, $subtask->reward)) {
            return $this->outputError(Constants::ERR_TASK_REWARD_FAILED, '任务奖励失败');
        }

        $incomeModel = new IncomeModel();
        if (!$incomeModel->create($mytask->uid, $mytask->task_id, $subtask->task_desc, $subtask->reward)) {
            return $this->outputError(Constants::ERR_TASK_INCOME_CREATE_FAILED, '任务添加收入记录失败');
        }
        $this->outputSuccess();
    }

}
