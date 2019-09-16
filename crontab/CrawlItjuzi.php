<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlItjuzi', 'run']);

class CrawlItjuzi {

    const SOURCE = 'itjuzi';
    const TAG_NAME = 'invest';
    const URL ='https://itjuzi.com/api/newsletter';
    const DAYS_PER_TIME = 10;

    public static function run() {
        $days = self::DAYS_PER_TIME;
        while ($days-- >= 0) {
            $time = date('Y-m-d', time() - $days * 86400);
            $data = self::fetchUrl(self::URL, $time);
            if (!$data) {
                echo "fetchUrl failed $url\n";
            } else {
                self::save($data);
            }
        }
        echo "CrawlItjuzi run done\n";
    }

    private static function fetchUrl($url, $time) {
        echo "fetch url $url\n";
        $curl = new \Curl\Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Charset', 'UTF-8');
        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $curl->post($url, json_encode(['time' => $time]));
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
            if ($eventModel->exists(self::SOURCE, $item['id'])) {
                continue;
            }
            $eventModel->create($item['id'], self::SOURCE, self::TAG_NAME, $item['title'], $item['des'], $item['create_time']);
            echo $item['id']." saved\n";
        }
        return true;
    }
}


