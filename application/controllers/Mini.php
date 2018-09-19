<?php
class MiniController extends \Explorer\ControllerAbstract {

    private $orderBys = ['id', 'dau', 'total_user'];

    public function indexAction() {
        $notices = [
            '柚子游戏盒子和奥特曼大战怪兽达成合作',
            '柚子游戏盒子和皇上吉祥2达成合作',
            '柚子游戏盒子和奇迹正版MU达成合作',
            '柚子游戏盒子和枪神先生达成合作',
            '柚子游戏盒子和修罗武神达成合作',
        ];
        $contact = '18618482206';
        return $this->outputSuccess(compact('notices', 'contact'));
    }

    public function feedAction() {
        $type = (int)$this->getRequest()->getQuery('type', 1);
        $orderBy = $this->getRequest()->getQuery('orderBy', 'id');
        $orderDir = (int)$this->getRequest()->getQuery('orderDir', 0);
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $pagesize = 20;
        $orderDir = $orderDir == 0 ? 'DESC' : 'ASC';

        if (!in_array($orderBy, $this->orderBys)) {
            return $this->outputError(Constants::ERR_MINI_ORDERBY_INVALID, '不支持此类排序方式');
        }

        $miniModel = new MiniModel();
        $minis = $miniModel->fetchAll($type, $orderBy, $orderDir, $page, $pagesize);
        $is_end = count($minis) < $pagesize;
        $this->outputSuccess(compact('minis', 'is_end'));
    }

    public function createAction() {
        $miniModel = new MiniModel();
        $id = $miniModel->create($this->getRequest()->getPost());
        $this->outputSuccess(compact('id'));
    }

    public function updateAction() {
        $id = $this->getRequest()->getQuery('id');
        $online = $this->getRequest()->getQuery('online');
        $miniModel = new MiniModel();
        $mini = $miniModel->fetch($id);
        if (!$mini) {
            return $this->outputError(Constants::ERR_MINI_NOT_EXISTS, '小程序不存在');
        }
        $miniModel->update($id, $online);
        $this->outputSuccess();
    }

}
