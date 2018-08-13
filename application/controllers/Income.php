<?php
class IncomeController extends \Explorer\ControllerAbstract {

    public function recordAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $page = $this->getRequest()->getQuery('page');
        $page = int($page);

        $incomeModel = new IncomeModel();
        $records = $incomeModel->fetch($this->uid, $page);
        $this->outputSuccess(compact('records'));
    }

}
