<?php
class ErrorController extends Yaf\Controller_Abstract {

	public function errorAction($exception) {
		$this->getView()->assign("exception", $exception);
	}
}
