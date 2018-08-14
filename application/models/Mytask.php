<?php
class MytaskModel extends AbstractModel {

    const TABLE = 'mytask';

    public function fetch($id) {
        $where['id'] = $id;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function fetchTasks($uid) {
        $where['uid'] = $uid;
        $where['is_subtask'] = 0;
        $tasks = $this->db->table(self::TABLE)->where($where)->getAll();
        $ret = [];
        foreach($tasks as $task) {
            $ret[$task->task_id] = $task;
        }
        return $ret;
    }

    public function fetchSubtasks($uid, $task_ids) {
        $where['uid'] = $uid;
        $tasks = $this->db->table(self::TABLE)->where($where)
            ->in('task_id', $task_ids)
            ->getAll();
        $ret = [];
        foreach($tasks as $task) {
            $ret[$task->task_id] = $task;
        }
        return $ret;
    }

    public function fetchTask($uid, $task_id) {
        $where['uid'] = $uid;
        $where['task_id'] = $task_id;
        $task = $this->db->table(self::TABLE)->where($where)->get();
        return $task;
    }

    public function completeSubtask($uid, $task_id, $screenshots) {
        $data = [
            'uid' => $uid,
            'task_id' => $task_id,
            'is_subtask' => 1,
            'screenshots' => $screenshots,
            'status' => Constants::STATUS_MYTASK_IN_REVIEW,
            'created_at' => time(),
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function approve($id, $approved) {
        $where['id'] = $id;
        if ($approved) {
            $update['status'] = Constants::STATUS_MYTASK_APPROVED;
        } else {
            $update['status'] = Constants::STATUS_MYTASK_UNAPPROVED;
        }
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    public function incrTask($uid, $task_id, $subtasks) {
        if ($task = $this->fetchTask($uid, $task_id)) {
            if ($task->completed_num + 1 == $subtasks) {
                $sql = 'update '.self::TABLE.' set completed_num=completed_num+1, status=? where uid=? and task_id=?';
                return $this->db->query($sql, [Constants::STATUS_MYTASK_APPROVED, $uid, $task_id]);
            } else {
                $sql = 'update '.self::TABLE.' set completed_num=completed_num+1 where uid=? and task_id=?';
                return $this->db->query($sql, [$uid, $task_id]);
            }
        }
        $data = [
            'uid' => $uid,
            'task_id' => $task_id,
            'is_subtask' => 0,
            'completed_num' => 1,
            'created_at' => time(),
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

}
