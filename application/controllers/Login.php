<?php
class LoginController extends \Explorer\ControllerAbstract {

    public function wxloginAction() {
        $code = $this->getRequest()->getQuery('code');
        if (empty($code)) {
            return $this->outputError(Constants::ERR_LOGIN_CODE_INVALID, 'code无效');
        }
        $openid = \Explorer\Weixin::jscode2session($code);
        if (!$openid) {
            return $this->outputError(Constants::ERR_LOGIN_CODE_INVALID, 'code无效');
        }
        $userModel = new UserModel();
        $user = $userModel->existsOpenid($openid);
        if (!$user) {
            $id = $userModel->createOpenid($openid);
            $user = $userModel->fetch($id);
        }
        $token = \Explorer\Utils::generateToken(32);
        $loginModel = new LoginModel();
        $loginModel->saveToken($user->id, $token);
        $this->outputSuccess(compact('token', 'user'));
    }

    public function verifyTokenAction() {
        $token = $this->getRequest()->getQuery('token');
        $loginModel = new LoginModel();
        if (!($id = $loginModel->verifyToken($token))) {
            return $this->outputError(Constants::ERR_LOGIN_WRONG_TOKEN, 'token无效');
        }
        $userModel = new UserModel();
        $user = $userModel->fetch($id);
        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($id);
        $this->outputSuccess(compact('user', 'wallet'));
    }

}

