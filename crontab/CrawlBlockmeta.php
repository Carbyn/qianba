<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlBlockmeta', 'run']);

class CrawlBlockmeta {

    const SOURCE = 'blockmeta';
    const TAG_NAME = 'blockchain';
    const URL ='https://app.blockmeta.com/w1/news/list?num=20&cat_id=4481&page=%d';
    const PAGES_PER_TIME = 10;

    public static function run() {
        $page = self::PAGES_PER_TIME;
        while ($page > 0) {
            $url = sprintf(self::URL, $page--);
            $data = self::fetchUrl($url);
            if (!$data) {
                echo "fetchUrl failed $url\n";
                break;
            }
            self::save($data);
        }
        echo "CrawlBlockmeta run done\n";
    }

    private static function fetchUrl($url) {
        echo "fetch url $url\n";
        $curl = new \Curl\Curl();
        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $curl->get($url);
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
        $data = array_reverse($data['list']);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = $item['id'];
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['title'], $item['content'], $item['post_date']);
            echo $oid." saved\n";
        }
        return true;
    }
}




