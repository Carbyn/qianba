<?php
class LoginController extends \Explorer\ControllerAbstract {

    public function wxloginAction() {
        $code = $this->getRequest()->getQuery('code');
        $inviteCode = $this->getRequest()->getQuery('invite_code');
        if (empty($code)) {
            return $this->outputError(Constants::ERR_LOGIN_CODE_INVALID, 'code无效');
        }
        $openid = \Explorer\Weixin::jscode2session($code);
        if (!$openid) {
            return $this->outputError(Constants::ERR_LOGIN_CODE_INVALID, 'code无效');
        }
        $userModel = new UserModel();
        $user = $userModel->existsOpenid($openid);
        $walletModel = new WalletModel();
        if (!$user) {
            $id = $userModel->createOpenid($openid);
            $userModel->genCode($id);
            $user = $userModel->fetch($id);
            $walletModel->create($id);
            $this->bindMaster($user, $inviteCode);
        }
        $token = \Explorer\Utils::generateToken(32);
        $loginModel = new LoginModel();
        $loginModel->saveToken($user->id, $token);
        $wallet = $walletModel->fetch($user->id);
        $this->outputSuccess(compact('token', 'user', 'wallet'));
    }

    public function verifyTokenAction() {
        $token = $this->getRequest()->getQuery('token');
        $loginModel = new LoginModel();
        if (!($id = $loginModel->verifyToken($token))) {
            return $this->outputError(Constants::ERR_LOGIN_WRONG_TOKEN, 'token无效');
        }
        $userModel = new UserModel();
        $user = $userModel->fetch($id);
        if (!$user) {
            return $this->outputError(Constants::ERR_LOGIN_WRONG_TOKEN, 'token无效');
        }
        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($id);
        $this->outputSuccess(compact('user', 'wallet'));
    }

    private function bindMaster($user, $inviteCode) {
        if ($inviteCode) {
            $userModel = new UserModel();
            $master = $userModel->fetchCode($inviteCode);
            if (!$master) {
                return false;
            }
            $mUid = $master->id;
        } else {
            $clientIP = \Explorer\IP::getClientIP();
            $tributeModel = new TributeModel();
            $mUid = $tributeModel->getMaster($clientIP);
            if (!$mUid) {
                return false;
            }
            $userModel = new UserModel();
            $master = $userModel->fetch($mUid);
            if (!$master) {
                return false;
            }
        }
        if ($mUid == $user->id) {
            return false;
        }
        if ($master->register_time > $user->register_time) {
            return false;
        }
        if ($tributeModel->fetchMaster($user->id)) {
            return false;
        }
        if (!$tributeModel->bind($mUid, Constants::TYPE_TRIBUTE_TUDI, $user->id, $user->name, 0)) {
            return false;
        }
        $userModel->incrTudi($mUid);

        $mmUid = $tributeModel->fetchMaster($mUid);
        if (!$mmUid) {
            return true;
        }
        $tributeModel->bind($mmUid, Constants::TYPE_TRIBUTE_TUSUN, $user->id, $user->name, 0);
        $userModel->incrTusun($mmUid);
        return true;
    }

}
