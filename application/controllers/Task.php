<?php
class TaskController extends \Explorer\ControllerAbstract {

    public function listAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        // todo
    }

}
