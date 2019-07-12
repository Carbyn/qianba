<?php
class WordsModel extends AbstractModel {

    const TABLE = 'words';

    public function fetchAll($page, $pagesize) {
        $offset = max(0, ($page - 1) * $pagesize);
        $words = $this->db->table(self::TABLE)->orderBy('id', 'DESC')
            ->limit($offset, $pagesize)->getAll();
        return $words;
    }

    public function fetch($id) {
        return $this->db->table(self::TABLE)->where('id', $id)->get();
    }

    public function exists($md5) {
        return $this->db->table(self::TABLE)->where('md5', $md5)->get();
    }

    public function create($text, $source, $md5) {
        $data = compact('text', 'source', 'md5');
        return $this->db->table(self::TABLE)->insert($data);
    }

}
