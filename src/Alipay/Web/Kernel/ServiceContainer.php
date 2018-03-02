<?php

namespace Alipay\Web\Kernel;

class ServiceContainer
{
    public $config;

    public $apiParams;
    public $sysParams;

    public $bizContentarr = array();
    public $bizContent;

    public $apiMethodName;

    public $rsaPrivateKey;

    public $rsaPrivateKeyFilePath;

    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    private $fileCharset = "UTF-8";

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->rsaPrivateKey = $this->config['merchant_private_key'];
        $this->gatewayUrl = $config['gatewayUrl'];
    }

    public function execute()
    {
        //组装系统参数
        $sysParams["app_id"] = $this->config['app_id'];
        $sysParams["method"] = $this->apiMethodName;
        $sysParams["format"] = 'JSON';
        $sysParams["return_url"] = $this->config['return_url'];
        $sysParams["charset"] = 'UTF-8';
        $sysParams["sign_type"] = $this->config['sign_type'];
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["version"] = '1.0';
        $sysParams["notify_url"] = $this->config['notify_url'];
        $this->sysParams = $sysParams;
        $apiParams['biz_content'] = $this->getBizContent();

        $totalParams = array_merge($apiParams, $sysParams);
        //待签名字符串
        $preSignStr = $this->getSignContent($totalParams);

        echo $preSignStr;die();

        //签名
        $totalParams["sign"] = $this->generateSign($totalParams, $this->config['signType']);

        // var_dump($totalParams);die();
        return $this->buildRequestForm($totalParams);
    }

    public function buildRequestForm($para_temp)
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->gatewayUrl . "?charset=" . trim($this->postCharset) . "' method='POST'>";
        foreach ($para_temp as $key => $val) {
            //$val = $this->characet($val, $this->postCharset);
            $val = str_replace("'", "&apos;", $val);
            //$val = str_replace("\"","&quot;",$val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";

        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }


    public function getBizContent()
    {
        if (!empty($this->bizContentarr)) {
            $this->bizContent = json_encode($this->bizContentarr, JSON_UNESCAPED_UNICODE);
        }
        return $this->bizContent;
    }

    public function generateSign($params, $signType = "RSA")
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    protected function sign($data, $signType = "RSA")
    {
        if ($this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            $priKey = $this->rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if (!$this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    public function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {

        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }
}