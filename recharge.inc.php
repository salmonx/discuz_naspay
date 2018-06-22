<?php


if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if(!$_G['uid']) {
    showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
$do = $_GET['do'] ? $_GET['do'] : 'index' ;
$pluginName = 'naspay';
$set = $_G['cache']['plugin'][$pluginName];
$set['setmin'] = 0;
$set['setmax'] = 10000;

$address = $set['address'];
$number = 0;
$fee = 0;
$orderid = "";

if($do == 'record'){
    $navtitle = lang('plugin/'.$pluginName, 'Recharge record');
    /*
    $setgroups = $set['groups'];
    $setgroupsr = unserialize($setgroups);
    if(!in_array($_G['groupid'],$setgroupsr)){
        showmessage(lang('plugin/'.$pluginName, 'You do not have permission to use this function in the user group'), NULL);
    }
    */

    $limit = 10;
    $num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('naspay_order')." WHERE uid = '".$_G['uid']."' ");
    $page = max(1, intval($_GET['page']));
    $start_limit = ($page - 1) * $limit;
    $url = 'plugin.php?id=naspay:recharge&do=record';
    $multipage = multi($num, $limit, $page, $url,0,4);
    $sql = "SELECT * FROM ".DB::table('naspay_order')." WHERE uid = '".$_G['uid']."' ORDER BY creation_time DESC LIMIT ".$start_limit." ,".$limit;
    $query = DB::query($sql);
    $list = array();
    while ($value = DB::fetch($query)){
        $list[] = $value;
    }
    $stater = array(
        0 => '<font color=red>'.lang('plugin/'.$pluginName, 'Wait for payment').'</font>',
        1 => '<font color=green>'.lang('plugin/'.$pluginName, 'Recharge success').'</font>',
    );
}elseif($do == 'index'){
    $navtitle = lang('plugin/'.$pluginName, 'Integral recharge');

    $setratio = $set['ratio'];
    $setextcredit = $set['extcredit'];

    $setextcredittitle = $_G['setting']['extcredits'][$setextcredit]['title'];
    //$setextcreditunit = $_G['setting']['extcredits'][$setextcredit]['unit'];
    $setextcreditunit = '';
    
    $address = $set['address'];

    if(submitcheck('tpaysubmit', 1)) {

        $number = intval($_POST['number']);
        $subject = lang('plugin/'.$pluginName, 'lang60').$_G['username'].lang('plugin/'.$pluginName, 'lang61').$setextcredittitle.$number.$setextcreditunit;//标题

        //生成订单
        $fee = $number/$setratio;
        $rand = rand(10000,99999);
        $orderid = date("YmdHis",$_G['timestamp']).$rand;
        $post = array(
            'id' => $orderid,
            'creation_time' => $_G['timestamp'],
            'uid' => $_G['uid'],
            'number' => $number,
            'fee' => $fee,
            'extcredit' => $setextcredit,
            'state' => 0,
            'finish_time' => '0',
            'subject' => $subject,
            'paytype' => '0'
        );

        if(DB::insert('naspay_order', $post)){

            $appid = $set['appid'];;
            $appsecret = $set['secret'];;
            $timestamp = $_G['timestamp'];
            $sign = md5($appid.$timestamp.$appsecret);
            $optional = array(
                'number' => $number,
                'extcredit' => $setextcredit,
            );
            $optional = json_encode($optional);
            $optional = urlencode($optional);
            $subject = diconv($subject, CHARSET, 'UTF-8');
            $subject = urlencode($subject);
            $para = array(
                'sign' => $sign,
                'appid' => $appid,
                'timestamp' => $timestamp,
                'total_fee' => $fee*100,//单位分
                'bill_no' => $orderid,
                'title' => $subject,
                'optional' => $optional,
                'return_url' => urlencode($_G['siteurl'].'plugin.php?id=naspay:recharge&do=record'),
                'notify_url' => urlencode($_G['siteurl'].'plugin.php?id=naspay:notify'),
                'bill_timeout' => '3600',//单位为秒
            );

            $para_get = createLinkstring($para);

            dheader('Location: ./plugin.php?id=naspay:recharge&do=pay&number='.$number.'&fee='.$fee. '&orderid='. $orderid);
        }
    }
}elseif ($do == 'pay') {
    $number = intval($_GET['number']);
    $fee = $_GET['fee'];
    $orderid = $_GET['orderid'];
    include template('naspay:'.$do);
    die();
}elseif ($do == 'payok'){

    // 支付确认， 增加点卡
    $orderid = addslashes($_GET['orderid']);
    include("config.php");

    $param = '{"from":"' .$dappaddress. '","to":"'. $dappaddress .'","value":"0","nonce":"0","gasPrice":"1000000","gasLimit":"2000000","contract":{"function":"query","args":"[\"'. $set['address'] .'\"]"}}';

    $resp = objtoarr(json_decode(request_post($mainurl, $param)));
    //var_dump($resp); 
    
    $result = $resp['result']['result'];

    if ($result !== '"null"') {
        $ret = json_decode($result);
        //var_dump($ret->records);
        
        foreach ($ret->records as $record) {
            if ($record[0] == $orderid ) {
                payok($record, $orderid);
                return;
            }
        }
    }

    $result = array(
        'code' => -1,
        'message' => "支付未成功，请稍等重试!"
    );
    echo json_encode($result);
    exit();
        
    
}else{
    exit('Access Denied');
}
include template('naspay:'.$do);

function createLinkstring($para) {
    $arg  = "";
    while (list ($key, $val) = each ($para)) {
        $arg.=$key."=".$val."&";
    }
    //去掉最后一个&字符
    $arg = substr($arg,0,count($arg)-2);

    //如果存在转义字符，那么去掉转义
    if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

    return $arg;
}


function payok($record, $bill_no){
    global $pluginName;

    //获取订单
    $order = DB::fetch_first("SELECT * FROM ".DB::table($pluginName.'_order')." WHERE id = '".$bill_no."'");


    if (intval($record[2]) !== intval($order['number'])) {
        $result = array(
            'code' => -1,
            'message' => '支付金额与购买数量不一致 '. intval($record[2]) .':'. $order['number'] .'请联系管理员处理！'
        );
        echo json_encode($result);
        exit();
    }
 
     //第二步:过滤重复
    if($order['state'] != 0){
        //客户需要根据订单号进行判重，忽略已经处理过的订单, state:1为没处理，其他状态则为处理过
        $result = array(
            'code' => -1,
            'message' => '已支付，请勿重复支付！'
        );
        echo json_encode($result);
        exit();
    }

    //更新订单信息
    $paytype = "NAS";

    DB::query("UPDATE ".DB::table($pluginName.'_order')." SET paytype = '".$paytype."', state = 1, finish_time = '".$_G['timestamp']."' WHERE id = '".$bill_no."'", 'UNBUFFERED');

    //更新用户积分
    updatemembercount($order['uid'], array($order['extcredit'] => $order['number']), 1, 'AFD', $order['uid']);

    //发送通知
    updatecreditbyaction($pluginName, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);

    notification_add($order['uid'], 'credit', 'addfunds', array(
        'orderid' => $bill_no,
        'price' => $total_fee,
        'value' => $_G['setting']['extcredits'][$order['extcredit']]['title'].' '.$order['number'].' '.$_G['setting']['extcredits'][$order['extcredit']]['unit']
    ), 1);

    $result = array(
        'code' => 200,
        'message' => 'success'
    );

    echo json_encode($result);
    exit();
    
}

?>