<?php
class Constants {

    const ERR_SYS_NOT_LOGGED                = 101;

    const ERR_LOGIN_CODE_INVALID            = 1001;
    const ERR_LOGIN_WRONG_TOKEN             = 1002;

    const ERR_TASK_DURATION_NOT_ENOUGH      = 2001;

    const ERR_UPLOADER_FAILED               = 3001;
    const ERR_UPLOADER_NO_IMAGE             = 3002;

    const ERR_USER_DATA_EMPTY               = 4001;
    const ERR_USER_BIND_FAILED              = 4002;
    const ERR_USER_CODE_INVALID             = 4003;

    const ERR_WITHDRAW_NO_RECEIPT           = 5001;
    const ERR_WITHDRAW_UPDATE_RECEIPT_FAILED= 5002;
    const ERR_WITHDRAW_BALANCE_NOT_ENOUGH   = 5003;
    const ERR_WITHDRAW_FAILED               = 5004;
    const ERR_WITHDRAW_RECORD_NOT_EXISTS    = 5005;
    const ERR_WITHDRAW_REVIEW_FAILED        = 5006;

    const ERR_EXPRESS_CODE_INVALID          = 6001;
    const ERR_EXPRESS_NO_SHIPPERS           = 6002;
    const ERR_EXPRESS_NO_TRACE              = 6003;

    const ERR_MINI_NOT_EXISTS               = 7001;
    const ERR_MINI_ORDERBY_INVALID          = 7002;

    const ERR_HELP_INCOME_NOT_EXIST         = 8001;



    const STATUS_MYTASK_IN_REVIEW           = 1;
    const STATUS_MYTASK_APPROVED            = 2;
    const STATUS_MYTASK_UNAPPROVED          = 3;

    const STATUS_TASK_OFFLINE               = 0;
    const STATUS_TASK_ONLINE                = 1;

    const STATUS_WITHDRAW_IN_REVIEW         = 1;
    const STATUS_WITHDRAW_APPROVED          = 2;
    const STATUS_WITHDRAW_UNAPPROVED        = 3;

    const TYPE_TRIBUTE_TUDI                 = 1;
    const TYPE_TRIBUTE_TUSUN                = 2;

    const TYPE_TASK_ONCE                    = 1;
    const TYPE_TASK_DAILY                   = 2;
    const TYPE_TASK_FOREVER                 = 3;

    const TASK_NEW                          = 101;
    const TASK_PIN                          = 102;
    const TASK_DAILY                        = 103;
    const TASK_HELP                         = 104;
    const TASK_INVITE                       = 105;

    const HELP_MAX = 3;
    const GAME_PLAY_VALID_DURATION = 60;
    const PRECISION = 100000;
    const PAGESIZE = 20;
    const CODE_DELTA = 83230210;

    const OS_ANDROID = 'android';
    const OS_IOS = 'ios';

    private static $env;
    public static function env() {
        if (!self::$env) {
            self::$env = 'dev';
            $envPath = APPLICATION_PATH.'/.env';
            if (@file_exists($envPath)) {
                $envConfig = new \Yaf\Config\Ini($envPath);
                if ($envConfig->env == 'production') {
                    self::$env = $envConfig->env;
                }
            }
        }
        return self::$env;
    }

}
