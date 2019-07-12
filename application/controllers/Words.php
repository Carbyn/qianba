<?php
class WordsController extends \Explorer\ControllerAbstract {

    public function feedAction() {
        $page = $this->getRequest()->getQuery('page', 1);
        $pagesize = 20;
        $wordsModel = new WordsModel();
        $words = $wordsModel->fetchAll($page, $pagesize);
        $is_end = count($words) < $pagesize;
        $this->outputSuccess(compact('words', 'is_end'));
    }

    public function detailAction() {
        $id = $this->getRequest()->getQuery('id');
        if (!$id) {
            return $this->outputError(Constants::ERR_WORDS_NOT_FOUND, '句子不存在');
        }
        $wordsModel = new WordsModel();
        $words = $wordsModel->fetch($id);
        if (!$words) {
            return $this->outputError(Constants::ERR_WORDS_NOT_FOUND, '句子不存在');
        }
        $this->outputSuccess(compact('words'));
    }

    public function addAction() {
        $text = trim($this->getRequest()->getPost('text'));
        $source = trim($this->getRequest()->getPost('source'));
        $md5 = md5($text);
        if (!$text || !$source) {
            return $this->outputError(Constants::ERR_WORDS_INVALID_REQUEST, '无效请求');
        }
        $wordsModel = new WordsModel();
        if ($wordsModel->exists($md5)) {
            return $this->outputError(Constants::ERR_WORDS_ALREADY_EXIST, '已存在');
        }
        $id = $wordsModel->create($text, $source, $md5);
        $this->outputSuccess(compact('id'));
    }

}
