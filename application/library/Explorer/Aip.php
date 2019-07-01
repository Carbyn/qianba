<?php
namespace Explorer;
class Aip {
    const APPID='16679096';
    const AK='YzGtAixPIpnGmgNqqMCVn65r';
    const SK='OODG68BsfzdolrMDwnsgNDps88AjxQFb';

    public static function getInstance() {
        $aip = new \Aip\AipSpeech(self::APPID, self::AK, self::SK);
        $aip->setConnectionTimeoutInMillis(5000);
        $aip->setSocketTimeoutInMillis(5000);
        return $aip;
    }
}
