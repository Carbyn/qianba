<?php
class PartnerController extends \Explorer\ControllerAbstract {

    public function calcAction() {
        $version_in_review = '1.0.0';
        $version = $this->getRequest()->getQuery('version');
        $aha = false;
        $jump = [
            [
                'logo' => 'https://qianba.1024.pm/static/youzi.png',
                'url' => 'wx69cafbf347e22ce7',
                'image' => 'https://qianba.1024.pm/static/qianba.png',
                'title' => '柚子游戏盒子',
                'desc' => '一个让你玩到停不下来的小游戏盒子',
            ],
        ];
        if ($version === $version_in_review) {
            $aha = true;
        }
        $this->outputSuccess(compact('aha', 'jump'));
    }

    public function wallpaperAction() {
        $version_in_review = '1.0.0';
        $version = $this->getRequest()->getQuery('version');
        $aha = false;
        $jump = [
            [
                'logo' => 'https://qianba.1024.pm/static/youzi.png',
                'url' => 'wx69cafbf347e22ce7',
                'image' => 'https://qianba.1024.pm/static/qianba.png',
                'title' => '柚子游戏盒子',
                'desc' => '一个让你玩到停不下来的小游戏盒子',
            ],
        ];
        if ($version === $version_in_review) {
            $aha = true;
        }
        $this->outputSuccess(compact('aha', 'jump'));
    }

    public function expressAction() {
        $version_in_review = '1.0.0';
        $version = $this->getRequest()->getQuery('version');
        $aha = false;
        $jump = [
            [
                'logo' => 'https://qianba.1024.pm/static/youzi.png',
                'url' => 'wx69cafbf347e22ce7',
                'image' => 'https://qianba.1024.pm/static/qianba.png',
                'title' => '柚子游戏盒子',
                'desc' => '一个让你玩到停不下来的小游戏盒子',
            ],
        ];
        if ($version === $version_in_review) {
            $aha = true;
        }
        $this->outputSuccess(compact('aha', 'jump'));
    }

}
