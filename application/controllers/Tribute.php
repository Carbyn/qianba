<?php
class TributeController extends \Explorer\ControllerAbstract {

    public function recordAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $type = (int)$this->getRequest()->getQuery('type', 1);
        $tributeModel = new TributeModel();
        $records = $tributeModel->fetchAll($this->uid, $type, $page);
        $is_end = count($records) < Constants::PAGESIZE;
        $this->outputSuccess(compact('records', 'is_end'));
    }

}
