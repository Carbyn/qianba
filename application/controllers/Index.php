<?php
class IndexController extends \Explorer\ControllerAbstract{

	public function indexAction() {
        $redis = new \Predis\Client();
        for ($i = 1; $i < 6; $i++) {
            $redis->del(md5('login_token_'.$i));
        }
        echo "hello";
	}

}
