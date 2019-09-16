<?php
class EventModel extends AbstractModel {

    const TABLE = 'event';

    public function fetchAll($tag, $page, $pagesize) {
        $where['tag'] = $tag;
        $offset = max(0, ($page - 1) * $pagesize);
        $events = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('published_at DESC, id DESC')
            ->limit($offset, $pagesize)
            ->getAll();
        foreach($events as &$event) {
            $event = (array)$event;
            $this->format($event);
        }
        return $events;
    }

    public function fetch($id) {
        $where['id'] = $id;
        $event = $this->db->table(self::TABLE)->where($where)->get();
        if ($event) {
            $event = (array)$event;
            $this->format($event);
        }
        return $event;
    }

    public function exists($source, $oid) {
        $where['source'] = $source;
        $where['oid'] = $oid;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function fetchMaxOid($source) {
        $where['source'] = $source;
        $event = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get();
        return $event ? $event->oid : 0;
    }

    public function create($oid, $source, $tag, $title, $description, $published_at) {
        $data = compact('oid', 'source', 'tag', 'title', 'description', 'published_at');
        return $this->db->table(self::TABLE)->insert($data);
    }

    private function format(&$event) {
        if ($event['tag'] == 'invest') {
            $event['display_time'] = date('m-d', $event['published_at']);
            // return;
        }
        $delta = time() - $event['published_at'];
        switch($delta) {
        case $delta < 3600:
            $event['display_time'] = round($delta / 60).'分钟前';
            break;
        case $delta < 86400:
            $event['display_time'] = round($delta / 3600).'小时前';
            break;
        default:
            $event['display_time'] = date('m-d H:i', $event['published_at']);
        }
    }

}
