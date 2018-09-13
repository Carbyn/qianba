<?php
namespace Explorer;
class Kdniao {

    const EBusinessID = '1380754';
    const AppKey = 'a4faf89e-16c4-4239-8ea4-5e4498f89aa3';
    const ReqURL = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';
    const REQ_TYPE_SEARCH = '1002';
    const REQ_TYPE_DISTINGUISH = '2002';

	public static function distinguish($LogisticCode){
        $requestData = json_encode(compact('LogisticCode'));
        return self::reqKdniao(self::REQ_TYPE_DISTINGUISH, $requestData);
    }

    public static function search($ShipperCode, $LogisticCode) {
        $OrderCode = '';
        $requestData = json_encode(compact('OrderCode', 'ShipperCode', 'LogisticCode'));
        return self::reqKdniao(self::REQ_TYPE_SEARCH, $requestData);
    }

    private static function reqKdniao($reqType, $requestData) {
		$data = array(
			'EBusinessID' => self::EBusinessID,
			'RequestType' => $reqType,
			'RequestData' => urlencode($requestData) ,
			'DataType' => '2',
		);
		$data['DataSign'] = self::encrypt($requestData, self::AppKey);
		$result = self::doPost(self::ReqURL, $data);
		return $result;
	}

    private static function doPost($url, $data) {
        $curl = new \Curl\Curl();
        $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->setOpt(CURLOPT_TIMEOUT, 2);
        $data = http_build_query($data);
        $curl->post($url, $data);
        if ($curl->error) {
            return false;
        }
        return $curl->response;
    }

	private static function encrypt($data, $appkey) {
		return urlencode(base64_encode(md5($data.$appkey)));
	}

}
