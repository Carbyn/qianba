<?php
class ArticleModel extends AbstractModel {

    const TABLE = 'gc_article';

    public function fetchAll($page, $pagesize) {
        $offset = max(0, ($page - 1) * $pagesize);
        $rows = $this->db->table(self::TABLE)->orderBy('id', 'DESC')
            ->limit($offset, $pagesize)->getAll();
        return $rows;
    }

    public function fetch($id) {
        return $this->db->table(self::TABLE)->where('id', $id)->get();
    }

    public function exists($source, $oid) {
        $where = compact('source', 'oid');
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function create($data) {
        return $this->db->table(self::TABLE)->insert($data);
    }

}
