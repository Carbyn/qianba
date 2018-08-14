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

    const ERR_UPLOADER_FAILED               = 3001;
    const ERR_UPLOADER_NO_IMAGE             = 3002;

    const ERR_USER_DATA_EMPTY               = 4001;

    const ERR_WITHDRAW_NO_RECEIPT           = 5001;
    const ERR_WITHDRAW_UPDATE_RECEIPT_FAILED= 5002;
    const ERR_WITHDRAW_BALANCE_NOT_ENOUGH   = 5003;
    const ERR_WITHDRAW_FAILED               = 5004;



    const STATUS_MYTASK_IN_REVIEW           = 1;
    const STATUS_MYTASK_APPROVED            = 2;
    const STATUS_MYTASK_UNAPPROVED          = 3;

    const STATUS_TASK_OFFLINE               = 1;

    const STATUS_WITHDRAW_IN_REVIEW         = 1;
    const STATUS_WITHDRAW_APPROVED          = 2;
    const STATUS_WITHDRAW_UNAPPROVED        = 3;
    const STATUS_WITHDRAW_FAILED            = 4;

}
