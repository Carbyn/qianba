
<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlUnsplash', 'run']);

class CrawlUnsplash {

    const UNSPLASH_URL = 'https://unsplash.com/napi/photos?page=%d&per_page=12&order_by=latest';
    const PAGES_PER_TIME = 10;

    public static function run() {
        $page = self::PAGES_PER_TIME;
        while ($page > 0) {
            $url = sprintf(self::UNSPLASH_URL, $page--);
            $data = self::fetchUrl($url);
            if (!$data) {
                echo "fetchUrl failed $url\n";
            } else {
                self::save($data);
            }
        }

        echo "CrawlUnsplash run done\n";
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
        $data = array_reverse($data);
        $wallpaperModel = new WallpaperModel();
        foreach($data as $item) {
            if ($wallpaperModel->exists($item['id'])) {
                continue;
            }
            $wallpaperModel->create($item['id'], $item['urls']['full'],
                $item['urls']['regular'], $item['urls']['small'],
                $item['user']['name'], $item['links']['html']);
            echo $item['id']." saved\n";
        }
        return true;
    }

}
