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
        $shipper['ShipperLogo'] = $this->getLogo($shipper['ShipperCode']);
        $traces = \Explorer\Kdniao::search($shipper['ShipperCode'], $code);
        $traces = @json_decode($traces, true);
        if (!$traces) {
            return $this->outputError(Constants::ERR_EXPRESS_NO_TRACE, '暂时没有更多信息');
        }
        $traces = array_reverse($traces['Traces']);
        $this->outputSuccess(compact('shipper', 'traces'));
    }

    private function getLogo($ShipperCode) {
        $logos = [
            'SF'        => 'https://qianba.1024.pm/static/express/shunfeng.png', //顺丰速运    SF
            'HTKY'      => 'https://qianba.1024.pm/static/express/huitongkuaidi.png', //百世快递    HTKY
            'ZTO'       => 'https://qianba.1024.pm/static/express/zhongtong.png', //中通快递    ZTO
            'STO'       => 'https://qianba.1024.pm/static/express/shentong.png', //申通快递    STO
            'YTO'       => 'https://qianba.1024.pm/static/express/yuantong.png', //圆通速递    YTO
            'YD'        => 'https://qianba.1024.pm/static/express/yunda.png', //韵达速递    YD
            'YZPY'      => 'https://qianba.1024.pm/static/express/youzhengguonei.png', //邮政快递包裹    YZPY
            'EMS'       => 'https://qianba.1024.pm/static/express/ems.png', //EMS    EMS
            'HHTT'      => 'https://qianba.1024.pm/static/express/tiantian.png', //天天快递    HHTT
            'JD'        => 'https://qianba.1024.pm/static/express/jd.png', //京东快递    JD
            'UC'        => 'https://qianba.1024.pm/static/express/youshuwuliu.png', //优速快递    UC
            'DBL'       => 'https://qianba.1024.pm/static/express/debangwuliu.png', //德邦快递    DBL
            'ZJS'       => 'https://qianba.1024.pm/static/express/zhaijisong.png', //宅急送    ZJS
            'TNT'       => 'https://qianba.1024.pm/static/express/tnt.png', //TNT快递    TNT
            'UPS'       => 'https://qianba.1024.pm/static/express/ups.png', //UPS    UPS
            'DHL'       => 'https://qianba.1024.pm/static/express/dhl.png', //DHL    DHL
            'FEDEX'     => 'https://qianba.1024.pm/static/express/fedex.png', //FEDEX联邦(国内件）    FEDEX
            'FEDEX_GJ'  => 'https://qianba.1024.pm/static/express/fedex.png', //FEDEX联邦(国际件）    FEDEX_GJ
        ];
        // todo
        return isset($logos[$ShipperCode]) ? $logos[$ShipperCode] : 'https://qianba.1024.pm/static/express/default.png';
    }

}
