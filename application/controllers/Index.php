<?php
class IndexController extends \Explorer\ControllerAbstract{

	public function indexAction() {
        $code = $this->getRequest()->getQuery('code');
        $truthCode = '';
        if ($code) {
            $userModel = new UserModel();
            $user = $userModel->fetchCode($code);
            if ($user) {
                $clientIP = \Explorer\IP::getClientIP();
                $tributeModel = new TributeModel();
                $tributeModel->storeMaster($clientIP, $user->id);
                $truthCode = $code;
            }
        }
        $this->getView()->assign('code', $truthCode);
        $this->getView()->display('index/index.phtml');
	}

}
