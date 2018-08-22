<?php
class TaskModel extends AbstractModel {

    const TABLE = 'task';

    public function createTask($name, $os, $reward, $images) {
        $reward = $reward * Constants::PRECISION;
        $os = $os == Constants::OS_ANDROID ? 0 : 1;
        $data = compact('name', 'os', 'reward', 'images');
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function createSubtask($name, $parent_id, $task_desc, $url, $code, $reward, $app_reward, $images, $demos) {
        $reward = $reward * Constants::PRECISION;
        $app_reward = $app_reward * Constants::PRECISION;
        $data = compact('name', 'parent_id', 'task_desc', 'url', 'code', 'reward', 'app_reward', 'images', 'demos');
        $id = $this->db->table(self::TABLE)->insert($data);
        if ($id) {
            $sql = 'update '.self::TABLE.' set subtasks=subtasks+1 where id=?';
            $this->db->query($sql, [$parent_id]);
            return $id;
        }
        return false;
    }

    public function fetchTasks($online = true, $os = Constants::OS_ANDROID) {
        $where['parent_id'] = 0;
        if ($online) {
            $where['status'] = Constants::STATUS_TASK_ONLINE;
        }
        $where['os'] = $os == Constants::OS_ANDROID ? 0 : 1;
        $tasks = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('id', 'DESC')
            ->getAll();

        $ret = [];
        foreach($tasks as $task) {
            $task->reward = $task->reward > 0 ? sprintf('%.2f', $task->reward/Constants::PRECISION) : 0;
            $task->app_reward = $task->app_reward > 0 ? sprintf('%.2f', $task->app_reward/Constants::PRECISION) : 0;
            $ret[$task->id] = (array)$task;
        }
        return $ret;
    }

    public function fetchSubtasks($id) {
        $where['parent_id'] = $id;
        $tasks = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('id', 'ASC')
            ->getAll();

        $ret = [];
        foreach($tasks as $task) {
            $task->reward = $task->reward > 0 ? sprintf('%.2f', $task->reward/Constants::PRECISION) : 0;
            $task->app_reward = $task->app_reward > 0 ? sprintf('%.2f', $task->app_reward/Constants::PRECISION) : 0;
            $ret[$task->id] = (array)$task;
        }
        return $ret;
    }

    public function fetch($id) {
        $where['id'] = $id;
        $task = $this->db->table(self::TABLE)->where($where)->get();
        if ($task) {
            $task->reward = $task->reward > 0 ? sprintf('%.2f', $task->reward/Constants::PRECISION) : 0;
            $task->app_reward = $task->app_reward > 0 ? sprintf('%.2f', $task->app_reward/Constants::PRECISION) : 0;
        }
        return $task;
    }

    public function batchFetch($ids) {
        $tasks = $this->db->table(self::TABLE)->in('id', $ids)->getAll();
        foreach($tasks as &$task) {
            $task->reward = $task->reward > 0 ? sprintf('%.2f', $task->reward/Constants::PRECISION) : 0;
            $task->app_reward = $task->app_reward > 0 ? sprintf('%.2f', $task->app_reward/Constants::PRECISION) : 0;
        }
        return $tasks;
    }

    public function update($id, $online) {
        $where['id'] = $id;
        $orWhere['parent_id'] = $id;
        if ($online) {
            $update['status'] = Constants::STATUS_TASK_ONLINE;
        } else {
            $update['status'] = Constants::STATUS_TASK_OFFLINE;
        }
        return $this->db->table(self::TABLE)->where($where)
            ->orWhere($orWhere)
            ->update($update);
    }

}
