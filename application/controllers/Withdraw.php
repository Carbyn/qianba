<?php
class WithdrawController extends \Explorer\ControllerAbstract {

    public function submitAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $amount = (int)$this->getRequest()->getPost('amount');
        $receipt = $this->getRequest()->getPost('receipt');
        if (!$receipt) {
            return $this->outputError(Constants::ERR_WITHDRAW_NO_RECEIPT, '请提交收款人');
        }

        $walletModel = new WalletModel();
        $wallet = $walletModel->fetch($this->uid);
        if (!$wallet || $wallet->balance * Constants::PRECISION < $amount * Constants::PRECISION) {
            return $this->outputError(Constants::ERR_WITHDRAW_BALANCE_NOT_ENOUGH, '余额不足');
        }
        if ($wallet->receipt != $receipt
            && !$walletModel->updateReceipt($this->uid, $receipt)) {
            return $this->outputError(Constants::ERR_WITHDRAW_UPDATE_RECEIPT_FAILED, '更新收款人失败，请稍后重试');
        }
        if (!$walletModel->withdraw($this->uid, $amount)) {
            return $this->outputError(Constants::ERR_WITHDRAW_FAILED, '提现失败，请稍后重试');
        }

        $withdrawModel = new WithdrawModel();
        if (!$withdrawModel->create($this->uid, $amount)) {
            return $this->outputError(Constants::ERR_WITHDRAW_FAILED, '提现失败，请稍后重试');
        }

        $wallet = $walletModel->fetch($this->uid);
        $this->outputSuccess(compact('wallet'));
    }

    public function recordAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $withdrawModel = new WithdrawModel();
        $records = $withdrawModel->fetchAll($this->uid, $page);
        $is_end = count($records) < Constants::PAGESIZE;
        $this->outputSuccess(compact('records', 'is_end'));
    }

    public function reviewAction() {
        if (!$this->uid) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }

        $id = $this->getRequest()->getQuery('id');
        $approved = $this->getRequest()->getQuery('approved');
        $withdrawModel = new WithdrawModel();
        $record = $withdrawModel->fetch($id);
        if (!$record) {
            return $this->outputError(Constants::ERR_WITHDRAW_RECORD_NOT_EXISTS, '提现记录不存在');
        }
        if (!$withdrawModel->review($id, $approved)) {
            return $this->outputError(Constants::ERR_WITHDRAW_REVIEW_FAILED, '审核提现失败');
        }
        $this->outputSuccess();
    }

}
