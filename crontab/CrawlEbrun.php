<?php
include_once(dirname(__FILE__).'/Base.php');

$app->execute(['CrawlEbrun', 'run']);

class CrawlEbrun {

    const SOURCE = 'ebrun';
    const TAG_NAME = 'ecom';
    const URL ='http://m.ebrun.com/api/channel/column/column/calendar/%d';
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
        echo "CrawlEbrun run done\n";
    }

    private static function fetchUrl($url) {
        echo "fetch url $url\n";
        $curl = new \Curl\Curl();
        $curl->setHeader('Cookie', '_iebrunUu=1532571689732*1146282642*1740250880; _ebrunUu=1532571689737*461816602*548752479; UM_distinctid=164d46423e527e-0d6d4160303bba-6f300a7f-fa000-164d46423e626; Hm_lvt_87daad7faca22f66ec178d201d855ddb=1537617871; __adcookie=1537618089959*1321665247*1978210142; gr_user_id=2aa5863e-59c5-4162-a5cb-653b3e3c7ce0; src=http%3A//imgs.ebrun.com/wap/201506/close.png; _Jo0OQK=71999C936F460B4B3EFD1D67A4A1EFEBC67D7EEDE791508313B166F5B23091A3D7B67C2700F15867783085E1F9359BFECD24933C5EFC3A2D541F687B9ED6F15B664FFE6547E07A7FDBC3BBE16D8FE1D79EE3BBE16D8FE1D79EE88535F208AC7B144DEE21E36F8B280DAGJ1Z1Uw==; _iebrunUv=1537629049448*1340520485*519556726; _ebrunUv=1537629049463*1161874958*775038482; gr_session_id_866757ead57b5a28=f3fca424-90b9-4dcf-8cf0-5a0f8c206e63; gr_session_id_866757ead57b5a28_f3fca424-90b9-4dcf-8cf0-5a0f8c206e63=true; CNZZDATA30080313=cnzz_eid%3D815467552-1537614373-http%253A%252F%252Fm.ebrun.com%252F%26ntime%3D1537625248; sso_token_mobileebruncom=a8okkfrt448cg4s00scsgw8o0; Hm_lpvt_87daad7faca22f66ec178d201d855ddb=1537629065; XSRF-TOKEN=eyJpdiI6ImZxZk9KRjVcL0ptVHc4RzRTUjc4UDdnPT0iLCJ2YWx1ZSI6InJ6Q1N5WFVuMk9ZVklCVUF0T09Wb3BWVVpYa1BKM0wzUnFsck1pSmRFaGxYUWNDbkl4RVhcL3o4UDhcL1BcL09IeFQxa0p3TVBHdklDWnNobTNZOU1IXC9wdz09IiwibWFjIjoiMWY2NTY5MjQ2NGMxZGQwNjgzMTRmY2FmM2U3ZDc2ZWJmY2IyYjQ0YzUxYWEyN2JmNmRhZWUxMDM2MTQ5MzdhMiJ9; laravel_session=eyJpdiI6IkVzVUZiVzBRdDduTUZBYmJxeldzTkE9PSIsInZhbHVlIjoiRHdYaGZZVTBGZTc1eVVQYWhsSXNraU5oZVhxWXVYNVBjZnh5eU9cL2V0UzNPT2llQXNXb1ZFd1JBemRlZXdLQTkyaTRXV1laamRBZUEyUjNCNE9QY0x3PT0iLCJtYWMiOiIyMmUxNjkxMTdjMTZjODA2OGNmYWEyZTAyNzk1M2I3NTM4MmY5ZWY1YTBjNTc4NzEyODY4ZTBjYWFhYzcwZWY0In0%3D');
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
        $data = array_reverse($data['articleList']);
        $eventModel = new EventModel();
        foreach($data as $item) {
            $oid = $item['id'];
            if ($eventModel->exists(self::SOURCE, $oid)) {
                continue;
            }
            if (strpos($item['description'], '>>') !== false) {
                $item['description'] = '';
            }
            $eventModel->create($oid, self::SOURCE, self::TAG_NAME, $item['title'], $item['description'], strtotime($item['time']));
            echo $item['id']." saved\n";
        }
        return true;
    }
}


