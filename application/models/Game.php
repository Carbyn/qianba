<?php
class GameModel extends AbstractModel {

    const TABLE = 'game';

    public function create($data) {
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function update($id, $online) {
        $where['id'] = $id;
        $update['status'] = $online;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    public function fetch($id) {
        $where['id'] = $id;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function exists($name) {
        $where['name'] = $name;
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function batchFetch($ids) {
        $games = $this->db->table(self::TABLE)->in('id', $ids)->getAll();
        $ret = [];
        foreach($games as $game) {
            $ret[$game->id] = (array)$game;
        }
        return $ret;
    }

    public function fetchByCategory($category, $page, $pagesize) {
        $where['category'] = $category;
        $where['status'] = 1;
        $offset = max(0, ($page - 1) * $pagesize);
        $games = $this->db->table(self::TABLE)
            ->where($where)
            ->orderBy('appid DESC, id DESC')
            ->limit($offset, $pagesize)
            ->getAll();
        $ret = [];
        foreach($games as $game) {
            $ret[$game->id] = (array)$game;
        }
        return $ret;
    }

    public function fetchAll($page, $pagesize) {
        $offset = max(0, ($page - 1) * $pagesize);
        $games = $this->db->table(self::TABLE)
            ->orderBy('appid DESC, id DESC')
            ->limit($offset, $pagesize)
            ->getAll();
        $ret = [];
        foreach($games as $game) {
            $ret[$game->id] = (array)$game;
        }
        return $ret;
    }

}
