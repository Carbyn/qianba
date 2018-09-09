<?php
class WallpaperController extends \Explorer\ControllerAbstract {

    public function feedAction() {
        $page = (int)$this->getRequest()->getQuery('page', 1);
        $per_page = (int)$this->getRequest()->getQuery('per_page', 12);
        $wallpaperModel = new WallpaperModel();
        $wallpapers = $wallpaperModel->fetchAll($page, $per_page);
        $this->outputSuccess(compact('wallpapers'));
    }

}
