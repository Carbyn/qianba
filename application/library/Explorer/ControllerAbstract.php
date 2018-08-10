<?php
namespace Explorer;
class ControllerAbstract extends \Yaf\Controller_Abstract {

    public $userId;
    public $user;

    public function init() {
        // TODO
        if (isset($_COOKIE['carbyn'])) {
            $this->userId = 2;
            $userModel = new \UserModel();
            $this->user = $userModel->fetch($this->userId);
            return;
        }
        $token = isset($_COOKIE['token']) ? $_COOKIE['token'] : '';
        if ($token) {
            $loginModel = new \LoginModel();
            $this->userId = $loginModel->verifyToken($token);
            $userModel = new \UserModel();
            $this->user = $userModel->fetch($this->userId);
        } else {
            $this->userId = 0;
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
