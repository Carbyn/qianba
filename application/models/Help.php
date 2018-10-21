<?php
class HelpModel extends AbstractModel {

    const TABLE = 'help';
    const TTL_HELP = 86400 * 7;

    public function fetchAll($income_id) {
        $where['income_id'] = $income_id;
        $help = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('id ASC')
            ->getAll();

        foreach($help as &$h) {
            $h = (array)$h;
        }
        return $help;
    }

    public function create($income_id, $uid) {
        $help = compact('income_id', 'uid');
        $help['created_at'] = time();
        return $this->db->table(self::TABLE)->insert($help);
    }

    public function startHelp($uid, $income_id) {
        $redis = new Predis\Client();
        $key = $this->getHelpKey($uid);
        $help_income_id = $redis->get($key);
        if ($help_income_id) {
            return false;
        }
        $redis->set($key, $income_id);
        $redis->expire($key, self::TTL_HELP);
        return true;
    }

    public function inHelping($uid) {
        $redis = new Predis\Client();
        $key = $this->getHelpKey($uid);
        $help_income_id = $redis->get($key);
        return $help_income_id;
    }

    public function endHelp($uid) {
        $redis = new Predis\Client();
        $key = $this->getHelpKey($uid);
        $redis->del($key);
        return true;
    }

    private function getHelpKey($uid) {
        return md5('help_key_'.$uid);
    }

}
