<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlXiaoyouxi100', 'run']);

class CrawlXiaoyouxi100 {

    const XIAOYOUXI100_URL = 'https://www.xiaoyouxi100.com/%s/index_%d.html';
    const PAGES_PER_TIME = 3;

    static $categories = ['xiuxian', 'dongzuo', 'sheji', 'juese', 'tiyu', 'celue', 'jingsu', 'qipai', 'yizhi', 'yangcheng', 'yinyue', 'ertong'];

    public static function run() {
        foreach(self::$categories as $category) {
            echo "Crawl $category start\n";
            $page = self::PAGES_PER_TIME;
            while ($page > 0) {
                $url = sprintf(self::XIAOYOUXI100_URL, $category, $page--);
                $games = self::fetch($url);
                if (!$games) {
                    echo "fetchUrl failed $url\n";
                } else {
                    self::save($games, $category);
                }
                sleep(60);
            }
            echo "Crawl $category done\n";
        }
        echo "CrawlXiaoyouxi100 run done\n";
    }

    private static function fetch($url) {
        echo "fetch $url\n";
        $gameUrls = self::fetchList($url);
        if (!$gameUrls) {
            return false;
        }
        $games = [];
        foreach($gameUrls as $gameUrl) {
            $game = self::fetchGame($gameUrl);
            if ($game) {
                $games[] = $game;
            }
        }
        return $games;
    }

    private static function fetchList($url) {
        echo "fetchList $url\n";
        $dom = new \SimpleHtmlDom\simple_html_dom();
        $html = self::fetchUrl($url);
        if (!$html) {
            echo "fetchList failed\n";
            return false;
        }
        $dom->load($html);
        $gameBoxes = $dom->find('.gamebox');
        unset($dom);
        $gameUrls = [];
        foreach($gameBoxes as $box) {
            $aNode = $box->find('.p-left a', 0);
            $gameUrls[] = $aNode->getAttribute('href');
            unset($aNode);
        }
        unset($gameBoxes);
        return $gameUrls;
    }

    private static function fetchGame($url) {
        echo "fetchGame $url\n";
        $dom = new \SimpleHtmlDom\simple_html_dom();
        $html = self::fetchUrl($url);
        if (!$html) {
            echo "fetchGame failed\n";
            return false;
        }
        $dom->load($html);
        $nameNode = $dom->find('.game-c h3', 0);
        $game['name'] = $nameNode->innertext();
        unset($nameNode);
        $iconNode = $dom->find('.game-img img', 0);
        $game['icon'] = $iconNode->getAttribute('src');
        unset($iconNode);
        $qrcodeNode = $dom->find('.qcode img', 0);
        $game['qrcode'] = $qrcodeNode->getAttribute('src');
        unset($qrcodeNode);
        unset($dom);
        if ($game['qrcode'] == '/images/xiaoyouxi100qcode.jpg') {
            return false;
        }
        return $game;
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

    private static function save($games, $category) {
        $games = array_reverse($games);
        $gameModel = new GameModel();
        foreach($games as $game) {
            if ($gameModel->exists($game['name'])) {
                continue;
            }
            $game['category'] = $category;
            $game['status'] = 1;
            // print_r($game);
            $gameModel->create($game);
            echo $game['name']." saved\n";
        }
        return true;
    }

}
