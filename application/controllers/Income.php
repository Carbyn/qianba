<?php
class IncomeController extends \Explorer\ControllerAbstract {

    public function recordAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $page = $this->getRequest()->getQuery('page');
        $page = (int)$page;

        $incomeModel = new IncomeModel();
        $records = $incomeModel->fetchAll($this->uid, $page);
        $is_end = count($records) < Constants::PAGESIZE;
        $this->outputSuccess(compact('records', 'is_end'));
    }

}
