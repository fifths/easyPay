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
}