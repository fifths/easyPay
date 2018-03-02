<?php

error_reporting(E_ALL & ~E_NOTICE);
require_once __DIR__ . '/../vendor/autoload.php';
ini_set('date.timezone', 'Asia/Shanghai');
$config = include('config.bak.php');

$app = \Alipay\Web\Factory::Web($config);
$data = [
    'out_trade_no' => time(),
    'product_code' => 'FAST_INSTANT_TRADE_PAY',
    'total_amount' => '0.01',
    'subject' => 'test',
    'body' => '这是一个测试单子'
];
$app->pagePay($data);
echo $app->execute();