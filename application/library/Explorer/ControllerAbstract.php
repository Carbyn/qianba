<?php
namespace Explorer;
class ControllerAbstract extends \Yaf\Controller_Abstract {

    public $uid;
    public $user;
    public $os;

    public function init() {
        $os = strtolower($this->getRequest()->getQuery('os'));
        if ($os == \Constants::OS_IOS) {
            $this->os = \Constants::OS_IOS;
        } else {
            $this->os = \Constants::OS_ANDROID;
        }
        // TODO
        if (isset($_COOKIE['carbyn'])) {
            $this->uid = 1;
            $userModel = new \UserModel();
            $this->user = $userModel->fetch($this->uid);
            return;
        }
        $token = isset($_COOKIE['token']) ? $_COOKIE['token'] : '';
        if ($token) {
            $loginModel = new \LoginModel();
            $this->uid = $loginModel->verifyToken($token);
            $userModel = new \UserModel();
            $this->user = $userModel->fetch($this->uid);
        } else {
            $this->uid = 0;
        }
    }

    public function outputError($status, $msg, $data = []) {
        $data = compact('status', 'msg', 'data');
        return $this->outputJson($data);
    }

    public function outputSuccess($data = []) {
        $data = [
            'status' => 0,
            'msg' => 'succ',
            'data' => $data,
        ];
        return $this->outputJson($data);
    }

    public function outputJson($data) {
        header('Content-Type: application/json;charset=utf-8');
        echo json_encode($data);
    }

}
