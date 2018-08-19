<?php
class WalletModel extends AbstractModel {

    const TABLE = 'wallet';

    public function fetch($uid) {
        $where['uid'] = $uid;
        $wallet = $this->db->table(self::TABLE)->where($where)->get();
        if ($wallet) {
            $wallet->balance = $wallet->balance > 0 ? number_format($wallet->balance/Constants::PRECISION, 3) : 0;
        }
    }

    public function create($uid) {
        $data = [
            'uid' => $uid,
            'balance' => 0,
            'income' => 0,
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function reward($uid, $amount) {
        $amount = $amount * Constants::PRECISION;
        $sql = 'update '.self::TABLE.' set balance=balance+?, income=income+?'.' where uid=?';
        $ret = $this->db->query($sql, [$amount, $amount, $uid]);
        return (bool)$ret;
    }

    public function withdraw($uid, $amount) {
        $amount = $amount * Constants::PRECISION;
        $sql = 'update '.self::TABLE.' set balance=balance-?'
            .' where uid=?';
        $ret = $this->db->query($sql, [$amount, $uid]);
        return (bool)$ret;
    }

    public function updateReceipt($uid, $receipt) {
        $where['uid'] = $uid;
        $update['receipt'] = $receipt;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

}
