<?php
class WithdrawModel extends AbstractModel {

    const TABLE = 'withdraw';

    public function fetch($id) {
        $where['id'] = $id;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function fetchAll($uid, $page) {
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

    public function create($uid, $amount) {
        $data = [
            'uid' => $uid,
            'amount' => $amount,
            'status' => Constants::STATUS_WITHDRAW_IN_REVIEW,
            'created_at' => time(),
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function review($id, $approved) {
        $where['id'] = $id;
        if ($approved) {
            $update['status'] = Constants::STATUS_WITHDRAW_APPROVED;
        } else {
            $update['status'] = Constants::STATUS_WITHDRAW_UNAPPROVED;
        }
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

}
