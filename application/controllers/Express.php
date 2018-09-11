<?php
class ExpressController extends \Explorer\ControllerAbstract {

    public function searchAction() {
        $code = $this->getRequest()->getQuery('code');
        if (!$code) {
            return $this->outputError(Constants::ERR_EXPRESS_CODE_INVALID, '单号无效，请确认后重新输入');
        }
        $shippers = \Explorer\Kdniao::distinguish($code);
        $shippers = @json_decode($shippers, true);
        if (!$shippers || !$shippers['Success'] || empty($shippers['Shippers'])) {
            return $this->outputError(Constants::ERR_EXPRESS_NO_SHIPPERS, '单号无效，请确认后重新输入');
        }
        $shipper = $shippers['Shippers'][0];
        $shippers = $this->getShippers();
        if (isset($shippers[$shipper['ShipperCode']])) {
            $shipper = $shippers[$shipper['ShipperCode']];
        } else {
            $shipper['ShipperLogo'] = 'https://qianba.1024.pm/static/youzi.png';
            $shipper['ShipperPhone'] = '';
        }
        $traces = \Explorer\Kdniao::search($shipper['ShipperCode'], $code);
        $traces = @json_decode($traces, true);
        $traces = array_reverse($traces['Traces']);
        $this->outputSuccess(compact('code', 'shipper', 'traces'));
    }

    public function shippersAction() {
        $shippers = array_values($this->getShippers());
        $this->outputSuccess(compact('shippers'));
    }

    private function getShipper($ShipperCode) {
        $shippers = $this->getShippers();

    }

    private function getShippers() {
        $shippers = [
            'SF' => [
                'ShipperCode' => 'SF',
                'ShipperName' => '顺丰速运',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/shunfeng.png',
                'ShipperPhone' => '95338',
            ],
            'ZTO' => [
                'ShipperCode' => 'ZTO',
                'ShipperName' => '中通快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/zhongtong.png',
                'ShipperPhone' => '95311',
            ],
            'STO' => [
                'ShipperCode' => 'STO',
                'ShipperName' => '申通快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/shentong.png',
                'ShipperPhone' => '95543',
            ],
            'YTO' => [
                'ShipperCode' => 'YTO',
                'ShipperName' => '圆通快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/yuantong.png',
                'ShipperPhone' => '95554',
            ],
            'EMS' => [
                'ShipperCode' => 'EMS',
                'ShipperName' => 'EMS',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/ems.png',
                'ShipperPhone' => '11183',
            ],
            'YZPY' => [
                'ShipperCode' => 'YZPY',
                'ShipperName' => '邮政包裹',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/youzhengguonei.png',
                'ShipperPhone' => '11183',
            ],
            'HTKY' => [
                'ShipperCode' => 'HTKY',
                'ShipperName' => '百世快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/huitongkuaidi.png',
                'ShipperPhone' => '95320',
            ],
            'YD' => [
                'ShipperCode' => 'YD',
                'ShipperName' => '韵达快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/yunda.png',
                'ShipperPhone' => '95546',
            ],
            'HHTT' => [
                'ShipperCode' => 'HHTT',
                'ShipperName' => '天天快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/tiantian.png',
                'ShipperPhone' => '4001-888-888',
            ],
            'JD' => [
                'ShipperCode' => 'JD',
                'ShipperName' => '京东快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/jd.png',
                'ShipperPhone' => '950616',
            ],
            'UC' => [
                'ShipperCode' => 'UC',
                'ShipperName' => '优速快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/youshuwuliu.png',
                'ShipperPhone' => '400-1111-119',
            ],
            'DBL' => [
                'ShipperCode' => 'DBL',
                'ShipperName' => '德邦快递',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/debangwuliu.png',
                'ShipperPhone' => '95353',
            ],
            'ZJS' => [
                'ShipperCode' => 'ZJS',
                'ShipperName' => '宅急送',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/zhaijisong.png',
                'ShipperPhone' => '400-6789-000',
            ],
            'TNT' => [
                'ShipperCode' => 'TNT',
                'ShipperName' => 'TNT',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/tnt.png',
                'ShipperPhone' => '800-820-9868',
            ],
            'UPS' => [
                'ShipperCode' => 'UPS',
                'ShipperName' => 'UPS',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/ups.png',
                'ShipperPhone' => '400-820-8388',
            ],
            'DHL' => [
                'ShipperCode' => 'DHL',
                'ShipperName' => 'DHL',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/dhl.png',
                'ShipperPhone' => '95380',
            ],
            'FEDEX' => [
                'ShipperCode' => 'FEDEX',
                'ShipperName' => 'FedEx',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/fedex.png',
                'ShipperPhone' => '400-886-1888',
            ],
            'FEDEX_GJ' => [
                'ShipperCode' => 'FEDEX_GJ',
                'ShipperName' => 'FedEx国际',
                'ShipperLogo' => 'https://qianba.1024.pm/static/express/fedex.png',
                'ShipperPhone' => '400-886-1888',
            ],
        ];
        return $shippers;
    }

}
