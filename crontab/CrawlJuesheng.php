<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlJuesheng', 'run']);

class CrawlJuesheng {

    const SOURCE = 'juesheng';
    const TAG_NAME = 'edu';
    const URL = 'http://www.juesheng.com/site/home/get_news.json?_render&min_id=%s';
    const URL_PAGE = 'http://www.juesheng.com';
    const START_ID = 1537953540058384;
    const PAGES_PER_TIME = 10;

    public static function run() {
        echo "CrawlJuesheng run\n";
        $startID = self::getStartID();
        if (!$startID) {
            return;
        }
        $page = self::PAGES_PER_TIME;
        while($page-- > 0) {
            $url = sprintf(self::URL, $startID);
            $events = self::fetchUrlJSON($url);
            if (empty($events)) {
                echo "events empty\n";
                break;
            }
            self::save($events);
            $event = array_pop($events['data']['list']);
            $startID = $event['sort_field'];
        }
        echo "CrawlJuesheng done\n";
    }

    private static function getStartID() {
        $html = self::fetchUrl(self::URL_PAGE);
        if (!$html) {
            echo "getStartID failed\n";
            return false;
        }
        $startPos = strpos($html, 'VAR.theNewsList = ') + strlen('VAR.theNewsList = ');
        $endPos = strpos($html, 'VAR.viewChannelname = ');
        $json = substr($html, $startPos, $endPos - $startPos);
        if (preg_match('#sort_field":(\d+),#', $json, $matches)) {
            return $matches[1];
        }
        return false;
    }

    private static function fetchUrl($url) {
        echo "fetchUrl $url\n";
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
        return $resp;
    }

    private static function fetchUrlJSON($url) {
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
        $resp = substr($resp, 0, strpos($resp, '--==|html|==--'));
        $resp = @json_decode($resp, true);
        if (!$resp) {
            return false;
        }
        return $resp;
    }

    private static function save($data) {
        $data = array_reverse($data['data']['list']);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = $item['sort_field'];
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['title'], $item['brief'], $item['time']);
            echo $item['id']." saved\n";
        }
        return true;
    }
}

