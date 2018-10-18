<?php
class HistoryModel extends AbstractModel {

    const TABLE = 'history';
    const MAX_GAME_NUM = 20;

    public function add($uid, $gameid) {
        $history = $this->fetch($uid);
        if (empty($history)) {
            $gameids = $gameid;
            $history = [
                'uid' => $uid,
                'gameids' => $gameids,
                'updated_at' => time(),
            ];
            return $this->db->table(self::TABLE)->insert($history);
        } else {
            $gameids = $this->lru($history->gameids, $gameid);
            $where['uid'] = $uid;
            $update = [
                'gameids' => $gameids,
                'updated_at' => time(),
            ];
            return $this->db->table(self::TABLE)->where($where)->update($update);
        }
    }

    public function fetch($uid) {
        $where['uid'] = $uid;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    private function lru($gameids, $gameid) {
        $gameids = explode(',', $gameids);

        $new = [];
        foreach($gameids as $gid) {
            if ($gid != $gameid) {
                $new[] = $gid;
            }
        }
        array_unshift($new, $gameid);

        if (count($new) > self::MAX_GAME_NUM) {
            $new = array_slice($new, 0, self::MAX_GAME_NUM);
        }

        return implode(',', $new);
    }

}
