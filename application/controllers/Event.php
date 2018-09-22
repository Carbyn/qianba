<?php
class EventController extends \Explorer\ControllerAbstract {

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
