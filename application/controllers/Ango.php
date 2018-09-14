<?php
class AngoController extends \Explorer\ControllerAbstract {

    const VERSION_IN_REVIEW = '1.0.100';
    const VERSION_IN_REVIEW_YOUZI = '1.0.4';

    public function ahaAction() {
        $ver = $this->getRequest()->getQuery('bhb');
        $app = $this->getRequest()->getQuery('app', 'qianba');
        $aha = false;
        if ($app == 'qianba' && $ver === self::VERSION_IN_REVIEW) {
            $aha = true;
        }
        if ($app == 'youzi' && $ver === self::VERSION_IN_REVIEW_YOUZI) {
            $aha = true;
        }
        $new = '';
        if ($app == 'qianba') {
            $new = 'wx69cafbf347e22ce7';
        }
        $texts = [
            "appname" => "柚子游戏盒子",
            "notice" => "“小猪钱吧”是一个能帮你用手机赚零花钱的小助手，按照引导完成任务，得到奖励即可提现。邀请好友，永久提成10%哦~",
            'share' => [
                "title" => '有人@你，发现一个让你玩到停不下来的小游戏盒子!',
                'path' => '/pages/index',
                "imageUrl" => "https://qianba.1024.pm/static/youzi.png",
            ],
            "wallet" => "我的零钱",
            "code" => "邀请码",
            "mycode" => "我的邀请码链接",
            "copycode" => "复制邀请码",
            "input_code" => "填写邀请码",
            "master" => "我的师父",
            "tudi" => "我的徒弟",
            "tusun" => "我的徒孙",
            "balance" => "余额",
            "income" => "累计",
            "withdraw" => "提现",
            "withdraw_amount" => "提现金额",
            "withdraw_tips" => [
                '24小时内到账',
                '在“我的零钱”页面可以查看明细和提现进度',
            ],
            "showoff" => "炫耀一下",
            "income_records" => "收入明细",
            "withdraw_records" => "提现记录",
            "demos" => "任务截图例子",
            "screenshots" => "上传任务截图",
            "bd" => "商务合作",
            "new_income" => "新入账",
        ];
        if ($this->os == Constants::OS_ANDROID) {
            $navlist = [
                [
                    'title' => '试玩小游戏',
                    'type' => Constants::TYPE_TASK_MINI,
                ],
                [
                    'title' => '下载APP',
                    'type' => Constants::TYPE_TASK_CPA,
                ],
            ];
        } else {
            $navlist = [
                [
                    'title' => '试玩小游戏',
                    'type' => Constants::TYPE_TASK_MINI,
                ],
            ];
        }

        return $this->outputSuccess(compact('aha', 'new', 'navlist', 'texts'));
    }

}
