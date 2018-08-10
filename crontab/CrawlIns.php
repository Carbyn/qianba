<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlIns', 'run']);

class CrawlIns {

    const INS_URL_CAT = 'https://www.instagram.com/graphql/query/?query_hash=ded47faa9a1aaded10161a2ff32abb6b&variables=%s';
    const FETCH_IMG_CMD = 'wget -t 5 -T 5 %s -O %s';
    const PAGES_PER_TIME = 10;

    private static $tmp;

    public static function run() {
        self::$tmp = dirname(__FILE__).'/tmp';
        if (!@file_exists(self::$tmp)) {
            @exec("mkdir -p ".self::$tmp);
        }
        exec('rm -rf '.self::$tmp.'/*');
        $end_cursor = '';
        $i = 0;
        while (true) {
            if ($i++ >= self::PAGES_PER_TIME) {
                echo "get enough nodes\n";
                break;
            }
            $vars = self::buildVariables($end_cursor);
            $url = sprintf(self::INS_URL_CAT, urlencode($vars));
            // echo "fetch: $url\n";
            // $resp = self::fetchUrl($url);
            $resp = self::curlUrl($url);
            if (!$resp) {
                echo "resp empty\n";
                break;
            }
            $edges = $resp['data']['hashtag']['edge_hashtag_to_media']['edges'];
            if (empty($edges)) {
                echo "edges empty\n";
                break;
            }
            foreach($edges as $edge) {
                if ($edge['node']['is_video']) {
                    continue;
                }
                if (self::exists($edge['node']['id'])) {
                    echo $edge['node']['id']." exists\n";
                    continue;
                }
                self::save($edge['node']);
            }
            $pageInfo = $resp['data']['hashtag']['edge_hashtag_to_media']['page_info'];
            if (!$pageInfo['has_next_page']) {
                echo "has_next_page false\n";
                break;
            }
            $end_cursor = $pageInfo['end_cursor'];
            echo "end_cursor: $end_cursor\n";
        }
    }

    private static function buildVariables($after = '') {
        if ($after == '') {
            // $after = 'AQCQJVWnWWqYKyRMFaWY7ZGCn5IkzhAEO-f_RlETUUMVG-YkNVBSXfDQy1qXjCJ5cUaAh2lDhe1k3fNMfjE6RWiFdMwcl_EN-okniM6VKoQbyA';
        }
        $tag_name = '猫チョコピーカンで猫助け';
        $first = 8;
        return json_encode(compact('tag_name', 'first', 'after'));
    }

    private static function curlUrl($url) {
		$out = self::$tmp.'/'.md5($url);
        $cmd = "curl -s '$url' -H 'user-agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1' -H 'cookie: sessionid=IGSC59769ee130c2fceb8f760b541ef8af15f07403424b175f8247292595187a935d%3ASnvpLqgmLziNb0UcxGk0RXaj9rt94bMM%3A%7B%22_auth_user_id%22%3A1549046332%2C%22_auth_user_backend%22%3A%22accounts.backends.CaseInsensitiveModelBackend%22%2C%22_auth_user_hash%22%3A%22%22%2C%22_platform%22%3A4%2C%22_token_ver%22%3A2%2C%22_token%22%3A%221549046332%3AtelyDvOCzYqV2FU9CccyDj9oueOM58ZA%3Ad7c5d41d7222dd717bd6fdf4c0fe295cc20ff0fda430f9ed2ad9d97004075690%22%2C%22last_refreshed%22%3A1528538296.8594250679%7D' > $out";
        echo $cmd."\n";
        exec($cmd);
		$resp = @file_get_contents($out);
        if (!$resp) {
            return false;
        }
        $resp = @json_decode($resp, true);
        if (!$resp || $resp['status'] != 'ok') {
            return false;
        }
        return $resp;
    }

    private static function fetchUrl($url) {
        $curl = new \Curl\Curl();
        $curl->setCookie('sessionid', 'IGSC59769ee130c2fceb8f760b541ef8af15f07403424b175f8247292595187a935d%3ASnvpLqgmLziNb0UcxGk0RXaj9rt94bMM%3A%7B%22_auth_user_id%22%3A1549046332%2C%22_auth_user_backend%22%3A%22accounts.backends.CaseInsensitiveModelBackend%22%2C%22_auth_user_hash%22%3A%22%22%2C%22_platform%22%3A4%2C%22_token_ver%22%3A2%2C%22_token%22%3A%221549046332%3AtelyDvOCzYqV2FU9CccyDj9oueOM58ZA%3Ad7c5d41d7222dd717bd6fdf4c0fe295cc20ff0fda430f9ed2ad9d97004075690%22%2C%22last_refreshed%22%3A1528538296.8594250679%7D');

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
        if ($resp['status'] != 'ok') {
            return false;
        }
        return $resp;
    }

    private static function exists($sid) {
        $articleModel = new ArticleModel();
        return $articleModel->sidExists($sid);
    }

    private static function save($node) {
        $articleModel = new ArticleModel();
        $author = self::getAuthor();
        $mobile = '';
        $type = ArticleModel::TYPE_DEFAULT;
        $event_time = '';
        $event_address = '';
        $reward = 0;
        $text = '';
        $pub_time = $node['taken_at_timestamp'];
        $sid = $node['id'];
        if (!empty($node['edge_media_to_caption']['edges'][0]['node']['text'])) {
            // TODO
            // $text = $node['edge_media_to_caption']['edges'][0]['node']['text'];
        }
        $id = $articleModel->publish($author, $mobile, $type, $event_time, $event_address, $reward, $text, $pub_time, $sid);
        if (!$id) {
            echo "save node failed\n";
            return false;
        }
        $image = self::fetchImg($node['thumbnail_src']);
        if (!$image) {
            echo "fetchImg failed\n";
            $articleModel->delete($id);
            return false;
        }
        if (!$articleModel->addImage($id, $image)) {
            echo "save addImage failed\n";
            $articleModel->delete($id);
            return false;
        }
        return true;
    }

    private static function getAuthor() {
        static $authors = [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11,
        ];
        return $authors[array_rand($authors)];
    }

    private static function fetchImg($img) {
	    $upload_path = APPLICATION_PATH.'/uploads';
	    if (!@file_exists($upload_path)) {
	        mkdir($upload_path);
	    }
        $tmp = explode('.', $img);
        $ext = '.'.$tmp[count($tmp) - 1];
        $img_name = uniqid(true).$ext;
        $img_path = $upload_path.'/'.$img_name;
        $cmd = sprintf(self::FETCH_IMG_CMD, $img, $img_path);
        echo $cmd."\n";
        @exec($cmd, $out, $status);
        if ($status != 0) {
            echo "fetchImg failed\n";
            return false;
        }
		$remote_path = str_replace('ubuntu', 'explorer', $img_path);
		$cmd = "rsync $img_path explorer@dev.1024.pm:$remote_path";
		echo $cmd."\n";
		@exec($cmd);
        $image = 'https://xunchong.1024.pm/uploads/'.$img_name;
        return $image;
    }

}
