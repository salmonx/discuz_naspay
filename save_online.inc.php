<?php


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

loadcache('plugin');

$pluginName = 'naspay';
$set = $_G['cache']['plugin'][$pluginName];
$address = $set['address'];
$ratio = $set['ratio'];

include("config.php");


$status = "未设置";

if ($set['address']) {

    $param = '{"from":"' .$dappaddress. '","to":"'. $dappaddress .'","value":"0","nonce":"0","gasPrice":"1000000","gasLimit":"2000000","contract":{"function":"query","args":"[\"'. $set['address'] .'\"]"}}';

    $resp = objtoarr(json_decode(request_post($mainurl, $param)));
    $result = $resp['result']['result'];
    
    if ($result && $result !== '"null"') {
        $ret = json_decode($result);
        //var_dump($ret);
        $status = "已经上链, 积分兑换比例:" . 1.0/floatval($ret->price);
    }else{
        $status = "未上链";
    }
}



include template('naspay:save_online');
?>