<?php
class TaskModel extends AbstractModel {

    const TABLE = 'task';

    public function createTask($task_desc, $reward, $images) {
        $data = compact('task_desc', 'reward', 'images');
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function createSubtask($parent_id, $task_desc, $reward, $images, $demos) {
        $data = compact('parent_id', 'task_desc,', 'reward', 'images', 'demos');
        if ($this->db->table(self::TABLE)->insert($data)) {
            $sql = 'update '.self::TABLE.' set subtasks=subtasks+1 where id=?';
            return $this->db->query($sql, [$parent_id]);
        }
        return false;
    }

    public function fetchTasks() {
        $where['parent_id'] = 0;
        return $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('id', 'DESC')
            ->getAll();
    }

    public function fetchSubtasks($id) {
        $where['parent_id'] = $id;
        $tasks = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('id', 'ASC')
            ->getAll();

        $ret = [];
        foreach($tasks as $task) {
            $ret[$task->id] = $ret;
        }
        return $ret;
    }

    public function fetch($id) {
        $where['id'] = $id;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function batchFetch($ids) {
        return $this->db->table(self::TABLE)->in('id', $ids)->getAll();
    }

    public function offline($id) {
        $where['id'] = $id;
        $orWhere['parent_id'] = $id;
        $update['status'] = Constants::STATUS_TASK_OFFLINE;
        return $this->db->table(self::TABLE)->where($where)
            ->orWhere($orWhere)
            ->update($update);
    }

}
