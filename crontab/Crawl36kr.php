<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['Crawl36kr', 'run']);

class Crawl36kr {

    const SOURCE = '36kr';
    const TAG_NAME = 'rec';
    const URL = 'https://36kr.com/api/newsflash?b_id=%d&per_page=20';
    const START_ID = 137514;

    public static function run() {
        $curID = self::getCurrentID();
        if ($curID) {
            $startID = $curID;
        } else {
            $startID = self::START_ID;
        }
        echo "Crawl36kr run\n";
        while(true) {
            $url = sprintf(self::URL, $startID);
            $events = self::fetchUrl($url);
            if (empty($events)) {
                echo "events empty\n";
                break;
            }
            if ($events['data']['items'][0]['id'] + 1 < $startID) {
                echo "no more new events\n";
                break;
            }
            self::save($events);
            $startID += 20;
        }
        echo "Crawl36kr done\n";
    }

    private static function getCurrentID() {
        $eventModel = new EventModel();
        $curID = $eventModel->fetchMaxOid(self::SOURCE);
        return $curID;
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
        $data = array_reverse($data['data']['items']);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = $item['id'];
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['title'], $item['description'], strtotime($item['published_at']));
            echo $item['id']." saved\n";
        }
        return true;
    }
}
