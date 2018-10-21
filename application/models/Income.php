<?php
class IncomeModel extends AbstractModel {

    const TABLE = 'income';

    public function fetch($id) {
        $where['id'] = $id;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function fetchAll($uid, $page) {
        $where['uid'] = $uid;
        $limit = Constants::PAGESIZE;
        $offset = ($page - 1) * $limit;
        $records = $this->db->table(self::TABLE)->where($where)
            ->orderBy('id', 'DESC')
            ->limit($offset, $limit)
            ->getAll();

        foreach($records as &$row) {
            $row->created_at = date('Y-m-d H:i:s', $row->created_at);
            $row->income = $row->income > 0 ? sprintf('%.5f', $row->income/Constants::PRECISION) : 0;
        }
        return $records;
    }

    public function create($uid, $task_desc, $income) {
        $data = [
            'uid' => $uid,
            'task_desc' => $task_desc,
            'income' => $income * Constants::PRECISION,
            'created_at' => time(),
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

}
