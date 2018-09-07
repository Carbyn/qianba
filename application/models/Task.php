<?php
class TaskModel extends AbstractModel {

    const TABLE = 'task';

    public function createTask($name, $type, $os, $task_desc, $url, $reward, $images, $demos, $inventory) {
        $reward = $reward * Constants::PRECISION;
        $os = $os == Constants::OS_ANDROID ? 0 : 1;
        $data = compact('name', 'type', 'os', 'task_desc', 'url', 'reward', 'images', 'demos', 'inventory');
        if ($type == Constants::TYPE_TASK_MINI) {
            $data['subtasks'] = 1;
        }
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function createSubtask($name, $type, $parent_id, $task_desc, $buttons, $url, $code, $reward, $app_reward, $images, $demos) {
        $reward = $reward * Constants::PRECISION;
        $app_reward = $app_reward * Constants::PRECISION;
        $data = compact('name', 'type', 'parent_id', 'task_desc', 'buttons', 'url', 'code', 'reward', 'app_reward', 'images', 'demos');
        $id = $this->db->table(self::TABLE)->insert($data);
        if ($id) {
            $sql = 'update '.self::TABLE.' set subtasks=subtasks+1 where id=?';
            $this->db->query($sql, [$parent_id]);
            return $id;
        }
        return false;
    }

    public function fetchTasks($type, $os) {
        $where = [
            'parent_id' => 0,
            'type' => $type,
            'status' => Constants::STATUS_TASK_ONLINE,
        ];
        if ($type == Constants::TYPE_TASK_CPA) {
            $where['os'] = $os == Constants::OS_ANDROID ? 0 : 1;
        }
        $tasks = $this->db->table(self::TABLE)
            ->where($where)
            ->where('inventory', '>', 0)
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
        $ret = [];
        foreach($tasks as $task) {
            $task->reward = $task->reward > 0 ? sprintf('%.2f', $task->reward/Constants::PRECISION) : 0;
            $task->app_reward = $task->app_reward > 0 ? sprintf('%.2f', $task->app_reward/Constants::PRECISION) : 0;
            $ret[$task->id] = (array)$task;
        }
        return $ret;
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

    public function decrInventory($id) {
        $sql = 'update '.self::TABLE.' set inventory=inventory-1 where id=?';
        return $this->db->query($sql, [$id]);
    }

}
