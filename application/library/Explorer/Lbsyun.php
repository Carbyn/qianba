<?php
namespace Explorer;
class Lbsyun {
    #const AK='SM0DUCxCqXxWjxxjrQlBuN5OhWfwu6y1';
    const AK='iyb8boSU2yl141mgahaCUTH1';
    static $weather_url = 'https://api.map.baidu.com/telematics/v3/weather?coord_type=gcj02&output=json&ak=%s&sn=&timestamp=%s&location=%s';
    static $geo_url = 'http://api.map.baidu.com/geocoder/v2/?location=%s,%s&output=json&pois=1&ak=%s';
    static $weather_path = APPLICATION_PATH.'/weather_data/';

    public static function weather($location) {
        $url = sprintf(self::$weather_url, self::AK, time(), urlencode($location));
        echo $url."\n";
        $curl = new \Curl\Curl();

        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $curl->get($url);
            if ($curl->error) {
                echo $curl->error_code."\n";
            } else {
                $resp = $curl->response;
                break;
            }
        }
        if (!$resp) {
            echo "curl $url failed\n";
            return;
        }
        $weather = json_decode($resp, true);
        self::save($location, $weather);
    }

    public static function fetch($location) {
        if (date('H') < 18) {
            $day1 = date('Ymd', time() - 86400);
            $day2 = date('Ymd', time());
            $day1_extra = [
                'title' => '昨天',
                'md' => date('n.d', time() - 86400),
                'day' => self::getDay(date('N', time() - 86400)),
            ];
            $day2_extra = [
                'title' => '今天',
                'md' => date('n.d', time()),
                'day' => self::getDay(date('N', time())),
            ];
        } else {
            $day1 = date('Ymd', time());
            $day2 = date('Ymd', time() + 86400);
            $day1_extra = [
                'title' => '今天',
                'md' => date('n.d', time()),
                'day' => self::getDay(date('N', time())),
            ];
            $day2_extra = [
                'title' => '明天',
                'md' => date('n.d', time() + 86400),
                'day' => self::getDay(date('N', time() + 86400)),
            ];
        }

        $day1_path = self::$weather_path.$day1.'/'.$location.'.json';
        $day2_path = self::$weather_path.$day2.'/'.$location.'.json';

        $day1_weather = $day2_weather = [];
        if (@file_exists($day1_path)) {
            $day1_weather = @json_decode(@file_get_contents($day1_path), true);
            $day1_weather = array_merge($day1_weather, $day1_extra);
        }
        if (@file_exists($day2_path)) {
            $day2_weather = @json_decode(@file_get_contents($day2_path), true);
            $day2_weather = array_merge($day2_weather, $day2_extra);
        }
        return compact('day1_weather', 'day2_weather');
    }

    private static function getDay($day_no) {
        static $days = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        return $days[$day_no - 1];
    }

    private static function save($location, $weather) {
        if ($weather['status'] != 'success') {
            echo "weather error";
            return;
        }
        $days = 0;
        if (empty($weather['results'])) {
            echo "weather results empty\n";
            return;
        }
        foreach($weather['results'][0]['weather_data'] as $day) {
            $path = self::$weather_path.date('Ymd', time() + 86400*$days++);
            if (!@file_exists($path)) {
                @mkdir($path, 0777, true);
            }
            $path .= '/'.$location.'.json';
            file_put_contents($path, json_encode($day));
        }
        echo "$location saved\n";
    }

    public static function geocoder($longitude, $latitude) {
        $url = sprintf(self::$geo_url, $latitude, $longitude, self::AK);
        $curl = new \Curl\Curl();

        $resp = '';
        $retry = 3;
        while ($retry-- > 0) {
            $curl->get($url);
            if ($curl->error) {
                echo $curl->error_code."\n";
            } else {
                $resp = $curl->response;
                break;
            }
        }
        if (!$resp) {
            echo "curl $url failed\n";
            return;
        }
        $address = json_decode($resp, true);
        if ($address['status'] != 0) {
            return '广州';
        }
        $city = preg_replace('/市$/', '', $address['result']['addressComponent']['city']);
        return $city;
    }

}
