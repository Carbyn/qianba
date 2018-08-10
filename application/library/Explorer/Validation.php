<?php
namespace Explorer;
class Validation {

    public static function isMobileValid($mobile) {
        $pattern = '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/';
        if (preg_match($pattern, $mobile)) {
            return true;
        }
        return false;
    }

}
