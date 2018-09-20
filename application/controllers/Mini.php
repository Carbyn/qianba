<?php
class MiniController extends \Explorer\ControllerAbstract {

    const VERSION_IN_REVIEW = '1.0.0';

    private $orderBys = ['id', 'dau', 'total_user'];

    public function indexAction() {
        $ver = $this->getRequest()->getQuery('bhb');
        $aha = false;
        if ($ver === self::VERSION_IN_REVIEW) {
            $aha = true;
        }
        $texts = [
            'appname' => '柚子换量推广',
            'index' => [
                'notices' => [
                    '柚子游戏盒子和枪神先生达成合作',
                    '柚子游戏盒子和超级忍者达成合作',
                    '柚子游戏盒子和修罗武神达成合作',
                    '柚子游戏盒子和逐日战神达成合作',
                    '柚子游戏盒子和大军师达成合作',
                    '柚子游戏盒子和九仙图达成合作',
                    '柚子游戏盒子和仙剑至尊达成合作',
                    '柚子游戏盒子和超人守卫达成合作',
                    '柚子游戏盒子和武圣传奇达成合作',
                    '柚子游戏盒子和奇迹正版MU达成合作',
                    '柚子游戏盒子和皇上吉祥2达成合作',
                ],
                'navlist' => [
                    '小游戏换量',
                    '渠道推广',
                ],
                'filters' => [
                    '最新',
                    '日活',
                    '注册',
                ],
                'empty_text' => '小游戏去哪儿了，快到碗里来~',
                'publish' => '发布',
                'publish_title' => '发布信息请加微信',
                'contact' => '18618482206',
                'share' => [
                    "title" => '专注小游戏推广100年',
                    'path' => '/pages/index',
                    "imageUrl" => "https://qianba.1024.pm/static/youzi.png",
                ],
            ],
            'detail' => [
                'pos' => '资源位置',
                'publisher' => '资源发布者',
                'contact' => '联系人',
                'mobile' => '联系电话',
                'company' => '公司名称',
                'base_info' => '基础信息',
                'user_attrs' => '用户属性',
                'total_user' => '总注册用户',
                'dau' => '日活量',
                'res_desc' => '资源描述',
                'buttons' => [
                    '分享',
                    '邀请合作',
                ],
                'cooperate_title' => '合作请加微信',
            ],
        ];
        return $this->outputSuccess(compact('aha', 'texts'));
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

    public function detailAction() {
        $id = (int)$this->getRequest()->getQuery('id');
        if (!$id) {
            return $this->outputError(Constants::ERR_MINI_NOT_EXISTS, '小程序不存在');
        }
        $miniModel = new MiniModel();
        $mini = $miniModel->fetch($id);
        if (!$mini) {
            return $this->outputError(Constants::ERR_MINI_NOT_EXISTS, '小程序不存在');
        }
        $this->outputSuccess(compact('mini'));
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
