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
        $user = $this->user;
        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($this->uid);
        $tributeModel = new TributeModel();
        $masterUid = $tributeModel->fetchMaster($this->uid);
        $master = [];
        if ($masterUid) {
            $userModel = new UserModel();
            $master = $userModel->fetch($masterUid);
        }
        $this->outputSuccess(compact('user', 'wallet', 'master'));
    }

    public function codeAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $code = $this->getRequest()->getPost('code');
        if (!$code) {
            return $this->outputError(Constants::ERR_USER_CODE_INVALID, '邀请码无效');
        }

        $userModel = new UserModel();
        $master = $userModel->fetchCode($code);
        if (!$master) {
            return $this->outputError(Constants::ERR_USER_CODE_INVALID, '邀请码无效');
        }
        if ($master->register_time > $this->user->register_time) {
            return $this->outputError(Constants::ERR_USER_BIND_FAILED, '师父注册时间不能晚于你');
        }
        $tributeModel = new TributeModel();
        if ($tributeModel->fetchMaster($this->uid)) {
            return $this->outputError(Constants::ERR_USER_BIND_FAILED, '已经有师父了');
        }
        if (!$tributeModel->bind($master->id, Constants::TYPE_TRIBUTE_TUDI, $this->uid, $this->user->name, 0)) {
            return $this->outputError(Constants::ERR_USER_BIND_FAILED, '填写邀请码失败');
        }
        $userModel->incrTudi($master->id);

        $mmUid = $tributeModel->fetchMaster($master->id);
        if ($mmUid) {
            $mm = $userModel->fetch($mmUid);
            $tributeModel->bind($mm->id, Constants::TYPE_TRIBUTE_TUSUN, $this->uid, $this->user->name, 0);
            $userModel->incrTusun($mm->id);
        }

        $this->outputSuccess(compact('master'));
    }

}
