<?php
class IndexController extends \Explorer\ControllerAbstract{

	public function indexAction() {
        $code = $this->getRequest()->getQuery('code');
        if ($code) {
            $userModel = new UserModel();
            $user = $userModel->fetchCode($code);
            if ($user) {
                $clientIP = \Explorer\IP::getClientIP();
                $tributeModel = new TributeModel();
                $tributeModel->storeMaster($clientIP, $user->id);
                $this->getView()->assign('code', $code);
            }
        }
        $this->getView()->display('index/index.phtml');
	}

}
