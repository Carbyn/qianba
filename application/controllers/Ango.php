<?php
class AngoController extends \Explorer\ControllerAbstract {

    const VERSION_IN_REVIEW = '1.0.80';

    public function ahaAction() {
        $ver = $this->getRequest()->getQuery('bhb');
        $aha = false;
        if ($ver === self::VERSION_IN_REVIEW) {
            $aha = true;
        }
        return $this->outputSuccess(compact('aha'));
    }

}
