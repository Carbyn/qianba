<?php
class WheelController extends \Explorer\ControllerAbstract {

    public function indexAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $wheelModel = new WheelModel();
        $prizes = $wheelModel->fetchPrizes();
        $turns = $wheelModel->fetchTurns($this->uid);
        $bonus = $wheelModel->fetchBonus($this->user->tudi_num);

        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($this->uid);

        $this->outputSuccess(compact('prizes', 'turns', 'bonus', 'wallet'));
    }

    public function turnAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $wheelModel = new WheelModel();
        $prize = $wheelModel->turn($this->uid);
        if (!$prize) {
            return $this->outputError(Constants::ERR_WHEEL_TURNS_RUNOUT, '今日机会用完了，做任务赚取更多奖励吧~');
        }

        $turns = $wheelModel->fetchTurns($this->uid);

        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($this->uid);

        $this->outputSuccess(compact('prize', 'turns', 'wallet'));
    }

    public function giveupAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $wheelModel = new WheelModel();
        $wheelModel->removeCard($uid);

        $this->outputSuccess();
    }

}
