<?php
class LoginModel extends AbstractModel {

    const TTL_TOKEN = 86400 * 365;

    public function saveToken($id, $token) {
        $redis = new Predis\Client();
        $key = $this->getTokenKey($token);
        $redis->set($key, $id);
        $redis->expire($key, self::TTL_TOKEN);
        return true;
    }

    public function verifyToken($token) {
        $redis = new Predis\Client();
        $key = $this->getTokenKey($token);
        $id = $redis->get($key);
        return $id;
    }

    private function getTokenKey($id) {
        return md5('login_token_'.$id);
    }

}
