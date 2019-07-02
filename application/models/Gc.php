<?php
class GcModel extends AbstractModel {

    const TABLE = 'gc';

    const URL = 'http://trash.lhsr.cn/sites/feiguan/trashTypes_2/TrashQuery.aspx?kw=%s';

    public function create($data) {
        return $this->db->table(self::TABLE)->insert($data);
    }

    public function update($garbage, $classification) {
        $where['garbage'] = $garbage;
        $update['classification'] = $classification;
        return $this->db->table(self::TABLE)->where($where)->update($update);
    }

    public function fetchAllNotFound() {
        return $this->db->table(self::TABLE)->where('classification', 0)->getAll();
    }

    public function fetchDB($garbage) {
        return $this->db->table(self::TABLE)->where('garbage', $garbage)
            ->notWhere('classification', 0)->get();
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
