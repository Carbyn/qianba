<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlBianews', 'run']);

class CrawlBianews {

    const SOURCE = 'bianews';
    const TAG_NAME = 'blockchain';
    const URL ='https://www.bianews.com/news/news_list?channel=flash&type=1';
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
        echo "CrawlBianews run done\n";
    }

    private static function fetchUrl($url, $page) {
        echo "fetch url $url page=$page\n";
        $curl = new \Curl\Curl();
        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $curl->post($url, ['page_no' => $page, 'page_size' => 20]);
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
            $event['id'] = $liNode->getAttribute('id');
            $event['title'] = $liNode->find('.title', 0)->innertext();
            $event['description'] = str_replace(['Bianews', ' ', "\n", '&nbsp;'], '', strip_tags($liNode->find('.content', 0)->innertext()));
            $event['time'] = substr($liNode->find('.pub_time', 0)->getAttribute('data-time'), 0, 10);
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
}



