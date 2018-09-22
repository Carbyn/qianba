<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlItjuzi', 'run']);

class CrawlItjuzi {

    const SOURCE = 'itjuzi';
    const TAG_NAME = 'invest';
    const URL ='https://www.itjuzi.com/tag_tree/get_fifter_news_info?page=%d';
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
        echo "CrawlItjuzi run done\n";
    }

    private static function fetchUrl($url) {
        echo "fetch url $url\n";
        $curl = new \Curl\Curl();
        $curl->setHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1');
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
        $data = array_reverse($data['data']);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = md5($item['com_new_url']);
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['com_new_name'], '', strtotime($item['com_new_year'].'-'.$item['com_new_month'].'-'.$item['com_new_day']));
            echo $item['com_id']." saved\n";
        }
        return true;
    }
}


