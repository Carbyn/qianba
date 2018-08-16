<?php
namespace Explorer;
class Weixin {

    const APPID = 'wxbb7ad0ec88d9efb4';
    const SECRET = '16e79ba8b4c35e934d93879f79404450';

    const JSCODE2SESSION_URL = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';

    public static function jscode2session($jscode) {
        $url = sprintf(self::JSCODE2SESSION_URL, self::APPID, self::SECRET, $jscode);
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
        if (empty($resp['openid'])) {
            return false;
        }
        return $resp['openid'];
    }

}
