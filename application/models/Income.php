<?php
class IncomeModel extends AbstractModel {

    const TABLE = 'income';

    public function fetch($uid, $page) {
        $where['uid'] = $uid;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $records = $this->db->table(self::TABLE)->where($where)
            ->orderBy('id', 'DESC')
            ->limit($offset, $limit)
            ->getAll();

        foreach($records as &$row) {
            $row->created_at = date('Y-m-d H:i:s', $row->created_at);
        }
        return $records;
    }

    public function create($uid, $task_id, $task_desc, $income) {
        $data = [
            'uid' => $uid,
            'task_id' => $task_id,
            'task_desc' => $task_desc,
            'income' => $income,
            'created_at' => time(),
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

}
