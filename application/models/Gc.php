<?php
class GcModel extends AbstractModel {

    const URL = 'http://trash.lhsr.cn/sites/feiguan/trashTypes_2/TrashQuery.aspx?kw=%s';

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
