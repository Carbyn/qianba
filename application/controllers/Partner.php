<?php
class PartnerController extends \Explorer\ControllerAbstract {

    public function calcAction() {
        $version_in_review = '1.0.13';
        $version = $this->getRequest()->getQuery('version');
        $aha = false;
        $jump = [
            [
                'logo' => 'https://qianba.1024.pm/uploads/logo.png',
                'url' => 'wxbb7ad0ec88d9efb4',
                'image' => 'https://qianba.1024.pm/uploads/qianba.jpeg',
                'title' => '小猪钱吧',
                'desc' => '一个让你玩到停不下来的小程序',
            ],
        ];
        if ($version === $version_in_review) {
            $aha = true;
        }
        $this->outputSuccess(compact('aha', 'jump'));
    }

}
