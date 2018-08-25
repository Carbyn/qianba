<?php
class UserModel extends AbstractModel {

    const TABLE = 'user';

    public function existsOpenid($openid) {
        $where['openid'] = $openid;
        $user = $this->db->table(self::TABLE)->where($where)->get();
        return $user;
    }

    public function fetch($id) {
        $where['id'] = $id;
        $user = $this->db->table(self::TABLE)->where($where)->get();
        return $user;
    }

    public function fetchAll($ids) {
        $users = $this->db->table(self::TABLE)->in('id', $ids)->getAll();
        $ret = [];
        foreach($users as $user) {
            $ret[$user->id] = $user;
        }
        return $ret;
    }

    public function fetchCode($code) {
        $where['code'] = (int)$code;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function createOpenid($openid) {
        $data['openid'] = $openid;
        $data['register_time'] = time();
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function updateProfile($id, $data) {
        $where['id'] = $id;
        $this->db->table(self::TABLE)->where($where)->update($data);
    }

    public function genCode($id) {
        $where['id'] = $id;
        $update['code'] = $id + Constants::CODE_DELTA;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    public function incrTudi($id) {
        $sql = 'update '.self::TABLE.' set tudi_num=tudi_num+1 where id=?';
        return $this->db->query($sql, [$id]);
    }

    public function incrTusun($id) {
        $sql = 'update '.self::TABLE.' set tusun_num=tusun_num+1 where id=?';
        return $this->db->query($sql, [$id]);
    }

}
