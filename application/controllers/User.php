<?php
class UserController extends \Explorer\ControllerAbstract {

    public function updateProfileAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $name = $this->getRequest()->getPost('name');
        $avatar = $this->getRequest()->getPost('avatar');
        $data = [];
        if ($name) {
            $data['name'] = $name;
        }
        if ($avatar) {
            $data['avatar'] = $avatar;
        }
        if (empty($data)) {
            return $this->outputError(Constants::ERR_USER_DATA_EMPTY, '更新内容为空');
        }
        $userModel = new UserModel();
        $userModel->updateProfile($this->uid, $data);
        $this->outputSuccess();
    }

    public function profileAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $userModel = new UserModel();
        $user = $userModel->fetch($this->uid);
        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($this->uid);
        $this->outputSuccess(compact('user', 'wallet'));
    }

}
