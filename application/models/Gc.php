<?php
class GcModel extends AbstractModel {

    const TABLE = 'gc';

    const URL = 'http://trash.lhsr.cn/sites/feiguan/trashTypes_2/TrashQuery.aspx?kw=%s';

    public function create($data) {
        $data['count'] = 1;
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function update($garbage, $classification) {
        $where['garbage'] = $garbage;
        $update['classification'] = $classification;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    public function incrCount($garbage) {
        $sql = 'update '.self::TABLE.' set count=count+1 where garbage=?';
        return $this->db->query($sql, [$garbage]);
    }

    public function fetchAll() {
        return $this->db->table(self::TABLE)->getAll();
    }

    public function fetchAllFound() {
        return $this->db->table(self::TABLE)->notWhere('classification', 0)->getAll();
    }

    public function fetchAllNotFound() {
        return $this->db->table(self::TABLE)->where('classification', 0)->getAll();
    }

    public function fetchBatch($classification, $batchSize) {
        $sql = 'select garbage,classification from '.self::TABLE.' where classification = '.$classification.' order by rand() limit '.$batchSize;
        return $this->db->query($sql);
    }

    public function fetchDB($garbage) {
        $row = $this->db->table(self::TABLE)->where('garbage', $garbage)->get();
        if ($row) {
            $this->incrCount($garbage);
        }
        if ($row->classification != 0) {
            return $row;
        }
        return null;
    }

    public function exists($garbage) {
        return $this->db->table(self::TABLE)->where('garbage', $garbage)->get();
    }

    public function fetch($query) {
        $curl = new \Curl\Curl();
        $url = sprintf(self::URL, urlencode($query));
        $curl->get($url);
        if ($curl->error) {
            return '';
        }
        $dom = new \SimpleHtmlDom\simple_html_dom();
        $dom->load($curl->response);
        $node = $dom->find('.info span', 0);
        if (!$node) {
            return '';
        }
        $result = trim($node->innertext());
        if ($result) {
            return $result;
        }
        return '';
    }

}
