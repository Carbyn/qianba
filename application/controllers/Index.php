<?php
class IndexController extends \Explorer\ControllerAbstract{

	public function indexAction() {
        $this->getView()->display('index/index.phtml');
	}

}
