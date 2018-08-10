<?php
namespace Explorer;
class Utils {

    public static function generateCode($length = 6) {
        return rand(1,9).str_pad(rand(0, 10 * ($length - 1)), $length - 1, '0', STR_PAD_LEFT);
    }

    public static function generateToken($length = 6) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $token;
    }

}
