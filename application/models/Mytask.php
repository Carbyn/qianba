<?php
class MytaskModel extends AbstractModel {

    const TABLE = 'mytask';
    const TTL_PROGRESS = 86400;

    public function fetch($uid, $task_id, $date = 0) {
        $where = compact('uid', 'task_id');
        if ($date > 0) {
            $where['date'] = $date;
        }
        $task = $this->db->table(self::TABLE)->where($where)->get();
        return $task;
    }

    public function create($uid, $task_id) {
        $data = [
            'uid' => $uid,
            'date' => date('Ymd'),
            'task_id' => $task_id,
            'created_at' => time(),
        ];
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function fetchProgress($uid) {
        $redis = new Predis\Client();
        $key = $this->getProgressKey($uid);
        $progress = $redis->get($key);
        $progress = @json_decode($progress, true);
        if (!$progress) {
            return [
                'gameids' => [],
                'game_count' => 0,
                'duration' => 0,
            ];
        }
        return $progress;
    }

    public function updateProgress($uid, $gameid, $duration) {
        $progress = $this->fetchProgress($uid);
        if (!in_array($gameid, $progress['gameids'])) {
            $progress['gameids'][] = $gameid;
            $progress['game_count'] += 1;
        }
        $progress['duration'] += $duration;

        $redis = new Predis\Client();
        $key = $this->getProgressKey($uid);
        $redis->set($key, json_encode($progress));
        $redis->expire($key, self::TTL_PROGRESS);

        return $progress;
    }

    private function getProgressKey($uid) {
        return md5('task_progress_'.$uid.'_'.date('Ymd'));
    }

}
