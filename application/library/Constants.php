<?php
class Constants {

    const ERR_SYS_NOT_LOGGED                = 101;

    const ERR_LOGIN_CODE_INVALID            = 1001;
    const ERR_LOGIN_WRONG_TOKEN             = 1002;

    const ERR_TASK_NOT_EXISTS               = 2001;
    const ERR_TASK_ALREADY_REVIEWED         = 2002;
    const ERR_TASK_APPROVED_FAILED          = 2003;
    const ERR_TASK_INCR_TASK_FAILED         = 2004;
    const ERR_TASK_REWARD_FAILED            = 2005;
    const ERR_TASK_INCOME_CREATE_FAILED     = 2006;
    const ERR_TASK_IN_REVIEW                = 2007;
    const ERR_TASK_UPDATE_FAILED            = 2008;
    const ERR_TASK_CREATE_INFO_INVALID      = 2009;
    const ERR_TASK_TRIBUTE_INCR_FAILED      = 2010;
    const ERR_TASK_TYPE_INVALID             = 2011;
    const ERR_TASK_SCREENSHOTS_LOST         = 2012;

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

    const ERR_GC_QUERY_INVALID              = 8001;
    const ERR_GC_AIP_FAILED                 = 8002;
    const ERR_GC_AUDIO_NOT_EXIST            = 8003;


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

    const TYPE_TASK_CPA                     = 1;
    const TYPE_TASK_MINI                    = 2;

    const PRECISION = 100;
    const PERCENT_TUDI = 0.1;
    const PERCENT_TUSUN = 0.1;
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
