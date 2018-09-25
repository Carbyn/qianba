<?php
class EventController extends \Explorer\ControllerAbstract {

    const VERSION_IN_REVIEW = '1.0.1';

    public function indexAction() {
        $ver = $this->getRequest()->getQuery('bhb');
        $aha = false;
        if ($ver === self::VERSION_IN_REVIEW) {
            $aha = true;
        }
        $this->outputSuccess(compact('aha'));
    }

    public function tagsAction() {
        $tags = [
            [
                'tag' => 'rec',
                'name' => '推荐',
            ],
            [
                'tag' => 'ecom',
                'name' => '电商',
            ],
            [
                'tag' => 'invest',
                'name' => '融资',
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
