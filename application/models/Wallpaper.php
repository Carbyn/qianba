<?php
class WallpaperModel extends AbstractModel {

    const TABLE = 'wallpaper';

    public function create($oid, $full, $regular, $small, $name, $source) {
        $wallpaper = compact('oid', 'full', 'regular', 'small', 'name', 'source');
        return $this->db->table(self::TABLE)->insert($wallpaper);
    }

    public function exists($oid) {
        $where['oid'] = $oid;
        return (bool)$this->db->table(self::TABLE)->where($where)->get();
    }

    public function fetchAll($page, $per_page) {
        $offset = ($page - 1) * $per_page;
        $wallpapers = $this->db->table(self::TABLE)
            ->orderBy('id', 'DESC')
            ->limit($offset, $per_page)
            ->getAll();
        return $wallpapers;
    }

}
