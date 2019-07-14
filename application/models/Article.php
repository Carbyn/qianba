<?php
class ArticleModel extends AbstractModel {

    const TABLE = 'gc_article';

    public function fetchAll($page, $pagesize) {
        $offset = max(0, ($page - 1) * $pagesize);
        $rows = $this->db->table(self::TABLE)->orderBy('id', 'DESC')
            ->limit($offset, $pagesize)->getAll();
        if (!empty($rows)) {
            foreach($rows as &$row) {
                $row = $this->format($row);
            }
        }
        return $rows;
    }

    public function fetch($id) {
        $article = $this->db->table(self::TABLE)->where('id', $id)->get();
        if ($article) {
            return $this->format($article);
        }
        return $article;
    }

    public function exists($source, $oid) {
        $where = compact('source', 'oid');
        return $this->db->table(self::TABLE)->where($where)->get();
    }

    public function create($data) {
        return $this->db->table(self::TABLE)->insert($data);
    }

    private function format($article) {
        $article->imgs = $article->imgs ? explode('|', $article->imgs) : [];
        $article->content = json_decode($article->content, true);
        $delta = time() - $article->published_at;
        switch($delta) {
        case $delta < 3600:
            $article->published_at = round($delta / 60).'分钟前';
            break;
        case $delta < 86400:
            $article->published_at = round($delta / 3600).'小时前';
            break;
        case $delta < 86400 * 7:
            $article->published_at = round($delta / 86400).'天前';
            break;
        default:
            $article->published_at = date('Y年m月d日', $article->published_at);
        }
        return $article;
    }

}
