<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlTraveldaily', 'run']);

class CrawlTraveldaily {

    const SOURCE = 'traveldaily';
    const TAG_NAME = 'travel';
    const URL ='https://www.traveldaily.cn/Home/Latest';
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
            $events = self::parseHtml($data);
            if (!$events) {
                echo "parseHtml failed $url\n";
                break;
            }
            self::save($events);
        }
        echo "CrawlTraveldaily run done\n";
    }

    private static function fetchUrl($url, $page) {
        echo "fetch url $url page=$page\n";
        $curl = new \Curl\Curl();
        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $curl->post($url, ['page' => $page]);
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
        return $resp;
    }

    private static function parseHtml($data) {
        $dom = new \SimpleHtmlDom\simple_html_dom();
        $dom->load($data);
        $liNodes = $dom->find('li');
        if (!$liNodes) {
            echo "liNodes not found\n";
            return false;
        }
        $events = [];
        foreach($liNodes as $liNode) {
            $event['id'] = $liNode->find('.childR a', 0)->getAttribute('href');
            $event['id'] = substr($event['id'], strrpos($event['id'], '/') + 1);
            $event['title'] = $liNode->find('.childR a', 0)->innertext();
            $event['description'] = $liNode->find('.childR p a', 0)->innertext();
            $event['time'] = self::parseTime($liNode->find('.time', 0)->innertext());
            $events[] = $event;
        }
        return $events;
    }

    private static function save($data) {
        $data = array_reverse($data);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = $item['id'];
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['title'], $item['description'], $item['time']);
            echo $item['id']." saved\n";
        }
        return true;
    }

    private static function parseTime($str) {
        if (preg_match('#(\d+)小时前#', $str, $matches)) {
            return time() - 3600 * $matches[1];
        } else if (preg_match('#昨天 (\d+):(\d+)#', $str, $matches)) {
            return strtotime(date('Y-m-d', time()-86400).' '.$matches[1].':'.$matches[2]);
        } else if (preg_match('#前天 (\d+):(\d+)#', $str, $matches)) {
            return strtotime(date('Y-m-d', time()-86400*2).' '.$matches[1].':'.$matches[2]);
        } else if (strtotime($str)) {
            return strtotime($str);
        } else {
            return time();
        }
    }

}




