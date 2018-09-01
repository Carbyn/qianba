<?php
class AngoController extends \Explorer\ControllerAbstract {

    const VERSION_IN_REVIEW = '1.0.92';

    public function ahaAction() {
        $ver = $this->getRequest()->getQuery('bhb');
        $aha = false;
        if ($ver === self::VERSION_IN_REVIEW) {
            $aha = true;
        }
        $notice = '“小猪钱吧”是一个能帮你用手机赚零花钱的小助手，按照引导完成任务，得到奖励即可提现。';
        $new = 'wx69410165e46aab0b';
        $new = '';
        return $this->outputSuccess(compact('aha', 'notice', 'new'));
    }

}
