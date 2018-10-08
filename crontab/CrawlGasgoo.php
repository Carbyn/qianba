<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlGasgoo', 'run']);

class CrawlGasgoo {

    const SOURCE = 'gasgoo';
    const TAG_NAME = 'car';
    const URL ='https://m.gasgoo.com/News/Index.aspx/GetNewsList';
    const PAGES_PER_TIME = 10;

    public static function run() {
        $page = self::PAGES_PER_TIME;
        while ($page > 0) {
            $url = self::URL;
            $data = self::fetchUrl($url, $page--);
            if (!$data) {
                echo "fetchUrl failed $url\n";
                break;
            }
            self::save($data);
        }
        echo "CrawlGasgoo run done\n";
    }

    private static function fetchUrl($url, $page) {
        echo "fetch url $url\n";
        $curl = new \Curl\Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $start = ($page - 1) * 10 + 1;
            $end = $start + 10;
            $curl->post($url, sprintf('{"start":%d,"classid":"1","end":%d}', $start, $end));
            if ($curl->error) {
                return false;
            } else {
                $resp = $curl->response;
                break;
            }
        }
        if (!$resp) {
            return false;
        }
        $resp = @json_decode($resp, true);
        if (!$resp) {
            return false;
        }
        return $resp;
    }

    private static function save($data) {
        $data = array_reverse($data['d']);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = $item['ArticleId'];
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['Title'], '', strtotime(str_replace('-', '/', $item['IssueTime'])));
            echo $oid." saved\n";
        }
        return true;
    }
}





