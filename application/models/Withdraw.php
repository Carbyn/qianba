<?php
class WithdrawModel extends AbstractModel {

    const TABLE = 'withdraw';

    public function fetch($id) {
        $where['id'] = $id;
        $record = $this->db->table(self::TABLE)->where($where)->get();
        $record = $this->format($record);
        return $record;
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
            $row = $this->format($row);
        }
        return $records;
    }

    public function latest($limit) {
        $where['status'] = Constants::STATUS_WITHDRAW_APPROVED;
        $records = $this->db->table(self::TABLE)->where($where)
            ->orderBy('id', 'DESC')
            ->limit(0, $limit)
            ->getAll();

        foreach($records as &$row) {
            $row = $this->format($row);
        }
        return $records;
    }

    public function create($uid, $amount) {
        $data = [
            'uid' => $uid,
            'amount' => (int)($amount * Constants::PRECISION),
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

    private function format($row) {
        if (!$row) {
            return $row;
        }
        $row->created_at = date('Y-m-d H:i:s', $row->created_at);
        $row->amount = $row->amount > 0 ? sprintf('%.2f', $row->amount/Constants::PRECISION) : 0;
        switch($row->status) {
            case Constants::STATUS_WITHDRAW_IN_REVIEW:
                $row->status = '审核中';
                break;
            case Constants::STATUS_WITHDRAW_APPROVED:
                $row->status = '提现成功';
                break;
            case Constants::STATUS_WITHDRAW_UNAPPROVED:
                $row->status = '审核未通过';
                break;
            default:
                $row->status = '审核未通过';
        }
        return $row;
    }

}
