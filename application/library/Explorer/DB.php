<?php
namespace Explorer;
class DB {

    public static $db;

    public static function getInstance() {
        if (empty(self::$db)) {
            $config = new \Yaf\Config\Ini(APPLICATION_PATH.'/conf/db.ini', \Constants::env());
            self::$db = new \Buki\Pdox($config->database->toArray());
        }
        return self::$db;
    }

}
