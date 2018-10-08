<?php
class EventController extends \Explorer\ControllerAbstract {

    const VERSION_IN_REVIEW = '1.0.1';

    public function indexAction() {
        $ver = $this->getRequest()->getQuery('bhb');
        $aha = false;
        if ($ver === self::VERSION_IN_REVIEW) {
            $aha = true;
        }
        $texts = [
            'share' => [
                "title" => '最新最全最有料的一手快讯，就在柚子大事件！',
                'path' => '/pages/index',
                "imageUrl" => "https://qianba.1024.pm/static/youzi.png",
            ],
        ];
        $this->outputSuccess(compact('aha', 'texts'));
    }

    public function tagsAction() {
        $tags = [
            [
                'tag' => 'rec',
                'name' => '推荐',
            ],
            [
                'tag' => 'blockchain',
                'name' => '区块链',
            ],
            [
                'tag' => 'car',
                'name' => '汽车',
            ],
            [
                'tag' => 'travel',
                'name' => '旅游',
            ],
            [
                'tag' => 'ecom',
                'name' => '电商',
            ],
            [
                'tag' => 'invest',
                'name' => '融资',
            ],
            [
                'tag' => 'edu',
                'name' => '教育',
            ],
        ];
        $this->outputSuccess(compact('tags'));
    }

    public function feedAction() {
        $tag = $this->getRequest()->getQuery('tag', 'rec');
        $page = $this->getRequest()->getQuery('page', 1);
        $pagesize = 20;
        $eventModel = new EventModel();
        $events = $eventModel->fetchAll($tag, $page, $pagesize);
        $is_end = count($events) < $pagesize;
        $this->outputSuccess(compact('events', 'is_end'));
    }

}
