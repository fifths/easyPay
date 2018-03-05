<?php

namespace Alipay\Web;

use Alipay\Web\Kernel\ServiceContainer;

class Application extends ServiceContainer
{

    public function pagePay($data)
    {
        $this->apiMethodName = 'alipay.trade.page.pay';
        $this->bizContentarr['out_trade_no'] = $data['out_trade_no'];
        $this->bizContentarr['product_code'] = $data['product_code'];
        $this->bizContentarr['total_amount'] = $data['total_amount'];
        $this->bizContentarr['subject'] = $data['subject'];
        echo $this->execute();
    }

    /**
     * 验签方法
     * @param $arr 验签支付宝返回的信息，使用支付宝公钥。
     * @return boolean
     */
    function check($arr)
    {
        $result = $this->rsaCheckV1($arr, '', $this->signType);
        return $result;
    }
}