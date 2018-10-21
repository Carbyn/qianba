<?php
class HelpController extends \Explorer\ControllerAbstract {

    public function detailAction() {
        $income_id = $this->getRequest()->getQuery('id');

        $incomeModel = new IncomeModel();
        $income = $incomeModel->fetch($income_id);
        if (!$income) {
            return $this->outputError(Constants::ERR_HELP_INCOME_NOT_EXIST, '助力任务不存在');
        }

        $userModel = new UserModel();
        $user = $userModel->fetch($income->uid);

        $is_mine = $this->uid == $income->uid;

        $helpModel = new HelpModel();
        $records = $helpModel->fetchAll($income_id);

        if (!empty($records)) {
            $uids = array_column('uid');
            $users = $userModel->fetchAll($uids);
            foreach($records as &$record) {
                $record['user'] = $users[$record['uid']];
            }
        }

        $is_end = count($records) >= Constants::HELP_MAX;

        $this->outputSuccess(compact('user', 'is_mine', 'income', 'records', 'is_end'));
    }

    public function startAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $income_id = $this->getRequest()->getQuery('id');

        $incomeModel = new IncomeModel();
        $income = $incomeModel->fetch($income_id);
        if (!$income) {
            return $this->outputError(Constants::ERR_HELP_INCOME_NOT_EXIST, '助力任务不存在');
        }

        $helpModel = new HelpModel();
        $ret = $helpModel->startHelp($this->uid, $income_id);

        $this->outputSuccess();
    }

}
