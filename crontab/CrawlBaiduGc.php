<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlBaiduGc', 'run']);

class CrawlBaiduGc {

	const SOURCE = 'baidu';
	const URL = 'https://www.baidu.com/sf/vsearch?word=%s&pd=realtime&tn=vsearch&pn=%d&sa=vs_tab&mod=5&p_type=1&data_type=json&atn=index&lid=%s';

	public static function run() {
        $pn = 100;
        $lid = self::genLid();
        $articleModel = new ArticleModel();
        while(true) {
            $url = sprintf(self::URL, '%E5%9E%83%E5%9C%BE%E5%88%86%E7%B1%BB', $pn, $lid);
            $data = self::fetchUrl($url, true);
            $data = @json_decode($data, true);
            if (empty($data) || empty($data['data']) || $data['errno'] != 0 || empty($data['data']['list'])) {
                echo "articles empty\n";
                break;
            }
            $list = array_reverse($data['data']['list']);
            foreach($list as $item) {
                if (strpos($item['url'], 'baijiahao') === false) {
                    echo "$url not baijiahao article, skip\n";
                    continue;
                }
                $oid = md5($item['url']);
                if ($articleModel->exists(self::SOURCE, $oid)) {
                    echo "article already exists\n";
                    continue;
                }

                $article['source'] = self::SOURCE;
                $article['oid'] = $oid;
                $article['title'] = strip_tags($item['title']);
                $article['imgs'] = isset($item['img']) ? implode('|', $item['img']) : '';
                $article['publisher'] = $item['subsitename_new'];
                $article['publisher_avatar'] = isset($item['avatar']) ? $item['avatar'] : '';
                $article['published_at'] = self::parseTime($item['posttime']);

                $content = self::fetchArticle($item['url']);
                if (!$content) {
                    echo "article empty\n";
                    continue;
                }
                $article['content'] = $content;
                $articleModel->create($article);
            }
            $pn -= 10;
            if ($pn < 0) {
                break;
            }
        }
	}

    private static function fetchArticle($url) {
        $html = self::fetchUrl($url);
        if (!$html) {
            echo "$url fetch html failed\n";
            return '';
        }
        $dom = new \SimpleHtmlDom\simple_html_dom();
        $dom->load($html);
        $mainContentNode = $dom->find('.mainContent', 0);
        if (!$mainContentNode) {
            echo "$url mainContent not found\n";
            return '';
        }

        $contents = [];
        foreach($mainContentNode->children as $childNode) {
            if ($spanNode = $childNode->find('span', 0)) {
                $contents[] = [
                    'type' =>'text',
                    'content' => trim(strip_tags($spanNode->innertext())),
                ];
            } else if ($imgNode = $childNode->find('img', 0)) {
                $contents[] = [
                    'type' => 'img',
                    'contents' => trim($imgNode->getAttribute('src')),
                ];
            }
        }
        if (empty($contents)) {
            echo "$url cannot parse html\n";
            return '';
        }
        return json_encode($contents);
    }

	private static function fetchUrl($url, $isAjax = false, $retry = 3) {
        echo "fetch $url\n";
        $curl = new \Curl\Curl();
		$curl->setopt(CURLOPT_TIMEOUT, 3);
		$curl->setHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1');
		if ($isAjax) {
			$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
		}
		$data = '';
		while ($retry-- > 0) {
			$curl->get($url);
			if (!$curl->error && $data = $curl->response) {
				break;
			}
		}
		if (!$data) {
			echo "fetchUrl failed: $url";
		}
		return $data;
	}

    private static function genLid() {
        $len = 19;
        $lid = rand(1,9);
        for ($i = 0; $i < $len - 1; $i++) {
            $lid .= rand(0, 9);
        }
        return $lid;
    }

    // 1分钟前 1小时前 1天前 2019年2月21日
    private static function parseTime($timestr) {
        if (($pos = mb_strpos($timestr, '分钟')) !== false) {
            $minutes = intval(substr($timestr, 0, $pos));
            return time() - $minutes * 60;
        }
        if (($pos = mb_strpos($timestr, '小时')) !== false) {
            $hours = intval(substr($timestr, 0, $pos));
            return time() - $hours * 3600;
        }
        if (($pos = mb_strpos($timestr, '天')) !== false) {
            $days = intval(substr($timestr, 0, $pos));
            return time() - $days * 86400;
        }
        if (preg_match('#(\d+)年(\d+)月(\d+)日#', $timestr, $matches) !== false) {
            return strtotime(implode('-', array_slice($matches[0], 1)));
        }
        return time();
    }

}
