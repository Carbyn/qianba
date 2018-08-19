<?php
class WithdrawModel extends AbstractModel {

    const TABLE = 'withdraw';

    public function fetch($id) {
        $where['id'] = $id;
        $record = $this->db->table(self::TABLE)->where($where)->get();
        if ($record) {
            $record->amount = $record->amount > 0 ? number_format($record->amount/Constants::PRECISION, 3) : 0;
        }
        return $record;
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
            $row->amount = $row->amount > 0 ? number_format($row->amount/Constants::PRECISION, 3) : 0;
        }
        return $records;
    }

    public function create($uid, $amount) {
        $data = [
            'uid' => $uid,
            'amount' => $amount * Constants::PRECISION ,
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
