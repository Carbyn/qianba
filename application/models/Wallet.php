<?php
class WalletModel extends AbstractModel {

    const TABLE = 'wallet';

    public function fetch($uid) {
        $where['uid'] = $uid;
        $wallet = $this->db->table(self::TABLE)->where($where)->get();
        return $wallet;
    }

    public function reward($uid, $amount) {
        $wallet = $this->fetch($uid);
        if ($wallet) {
            $sql = 'update '.self::TABLE.' set balance=balance+?, income=income+?'
                .' where uid=?';
            $ret = $this->db->query($sql, [$amount, $amount, $uid]);
            return (bool)$ret;
        }
        $data = [
            'uid' => $uid,
            'balance' => $amount,
            'income' => $amount,
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function withdraw($uid, $amount) {
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
