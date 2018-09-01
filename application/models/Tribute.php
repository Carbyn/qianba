<?php
class TributeModel extends AbstractModel {

    const TABLE = 'tribute';
    const TTL_MASTER = 600;

    public function fetch($uid, $ouid) {
        $where = compact('uid', 'ouid');
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function fetchMaster($ouid) {
        $where = [
            'ouid' => $ouid,
            'type' => Constants::TYPE_TRIBUTE_TUDI,
        ];
        $record = $this->db->table(self::TABLE)->where($where)->get();
        if ($record) {
            return $record->uid;
        }
        return false;
    }

    public function fetchAll($uid, $type, $page) {
        $where = compact('uid', 'type');
        $limit = Constants::PAGESIZE;
        $offset = ($page - 1) * $limit;
        $records = $this->db->table(self::TABLE)->where($where)
            ->orderBy('id', 'DESC')
            ->limit($offset, $limit)
            ->getAll();

        foreach($records as &$row) {
            $row->created_at = date('Y-m-d H:i:s', $row->created_at);
            $row->amount = $row->amount > 0 ? sprintf('%.2f', $row->amount/Constants::PRECISION) : 0;
        }
        return $records;
    }

    public function bind($uid, $type, $ouid, $oname, $amount) {
        $amount = $amount * Constants::PRECISION;
        $created_at = time();
        $data = compact('uid', 'type', 'ouid', 'oname', 'amount', 'created_at');
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function incrAmount($uid, $ouid, $amount) {
        $amount = $amount * Constants::PRECISION;
        $sql = 'update '.self::TABLE.' set amount=amount+? where uid=? and ouid=?';
        return $this->db->query($sql, [$amount, $uid, $ouid]);
    }

    public function updateOname($ouid, $oname) {
        $where['ouid'] = $ouid;
        $update['oname'] = $oname;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    public function storeMaster($ip, $uid) {
        $redis = new Predis\Client();
        $key = $this->getMasterKey($ip);
        $redis->set($key, $uid);
        $redis->expire($key, self::TTL_MASTER);
        return true;
    }

    public function getMaster($ip) {
        $redis = new Predis\Client();
        $key = $this->getMasterKey($ip);
        $uid = $redis->get($key);
        if ($uid) {
            $redis->del($key);
        }
        return $uid;
    }

    private function getMasterKey($ip) {
        return md5('tribute_master_ip_'.$ip);
    }

}
