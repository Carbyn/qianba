<?php
class MiniModel extends AbstractModel {

    const TABLE = 'mini';

    public function fetch($id) {
        $where['id'] = $id;
        $mini = $this->db->table(self::TABLE)->where($where)->get();
        if ($mini) {
            $this->format($mini);
        }
        return $mini;
    }

    public function fetchAll($type, $orderBy, $orderDir, $page, $pagesize) {
        $where['type'] = (int)$type;
        $where['status'] = 1;
        $offset = ($page - 1) * $pagesize;
        $minis = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy($orderBy, $orderDir)
            ->limit($offset, $pagesize)
            ->getAll();
        foreach($minis as &$mini) {
            $this->format($mini);
        }
        return $minis;
    }

    public function create($data) {
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function update($id, $online) {
        $where['id'] = $id;
        $update['status'] = $online;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    private function format(&$mini) {
        $mini = (array)$mini;
        $mini['pos'] = explode(',', $mini['pos']);
        $mini['mode'] = explode(',', $mini['mode']);
        $mini['mobile'] = substr($mini['mobile'], 0, 3).'****'.substr($mini['mobile'], 7);
        unset($mini['wechat'], $mini['qq']);
        $mini['total_user'] = $mini['total_user'] >= 1000 ? ($mini['total_user']/10000).'w' : $mini['total_user'];
        $mini['dau'] = $mini['dau'] >= 1000 ? ($mini['dau']/10000).'w' : $mini['dau'];
    }

}
