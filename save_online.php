<?php


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
$pluginName = 'naspay';
$set = $_G['cache']['plugin'][$pluginName];

if( $_GET['supplement'] == 'yes' && $_GET['bill_no'] && $_GET['formhash'] == formhash()){
    
    $bill_no = daddslashes($_GET['bill_no']);
    $paytype = lang('plugin/'.$pluginName, 'lang66');
    //获取订单
    $order = DB::fetch_first("SELECT * FROM ".DB::table($pluginName.'_order')." WHERE id = '".$bill_no."'");
    $total_fee = $order['fee'];
    
    if($order['state'] != 0){
        cpmsg(lang('plugin/'.$pluginName, 'lang52'), 'action=plugins&operation=config&identifier='.$pluginName.'&pmod=details', 'error');
    }
    
    /*更新订单信息*/
    DB::query("UPDATE ".DB::table($pluginName.'_order')." SET paytype = '".$paytype."', state = 1, finish_time = '".$_G['timestamp']."' WHERE id = '".$bill_no."'", 'UNBUFFERED');

    /*更新用户积分*/
    updatemembercount($order['uid'], array($order['extcredit'] => $order['number']), 1, 'AFD', $order['uid']);

    /*发送通知*/
    updatecreditbyaction($pluginName, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
    notification_add($order['uid'], 'credit', 'addfunds', array(
        'orderid' => $bill_no,
        'price' => $total_fee,
        'value' => $_G['setting']['extcredits'][$order['extcredit']]['title'].' '.$order['number'].' '.$_G['setting']['extcredits'][$order['extcredit']]['unit']
    ), 1);
    
    cpmsg(lang('plugin/'.$pluginName, 'lang53'), 'action=plugins&operation=config&identifier='.$pluginName.'&pmod=details', 'succeed');
}

$stater = array(
    0 => '<font color=red>'.lang('plugin/'.$pluginName, 'Wait for payment').'</font>',
    1 => '<font color=green>'.lang('plugin/'.$pluginName, 'Recharge success').'</font>',
);

showformheader('plugins&operation=config&identifier='.$pluginName.'&pmod=details');
showtableheader(lang('plugin/'.$pluginName, 'Recharge record'));
$limit = 50;
$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table($pluginName.'_order')." ");
$page = max(1, intval($_GET['page']));
$start_limit = ($page - 1) * $limit;
$url = 'admin.php?action=plugins&operation=config&identifier='.$pluginName.'&pmod=details';
$multipage = multi($num, $limit, $page, $url);
$sql = "SELECT * FROM ".DB::table($pluginName.'_order')." ORDER BY creation_time DESC LIMIT ".$start_limit." ,".$limit;
$query = DB::query($sql);
showsubtitle(array('',lang('plugin/'.$pluginName, 'Order number'),lang('plugin/'.$pluginName, 'lang65'),lang('plugin/'.$pluginName, 'Commit time'),lang('plugin/'.$pluginName, 'state'),lang('plugin/'.$pluginName, 'Over Time'),lang('plugin/'.$pluginName, 'user name'),lang('plugin/'.$pluginName, 'points'),lang('plugin/'.$pluginName, 'Recharge amount'),lang('plugin/'.$pluginName, 'Payment of NAS'),lang('plugin/'.$pluginName, 'lang49'),lang('plugin/'.$pluginName, 'lang50')));
while ($result = DB::fetch($query)){
    showtablerow('','', array(
        '',
        $result['id'],
        $result['subject'],
        date("Y-m-d H:i:s",$result['creation_time']),
        $stater[$result['state']],
        $result['finish_time'] ? date("Y-m-d H:i:s",$result['finish_time']) : '-',
        '<a href="home.php?mod=space&uid='.$result['uid'].'&do=profile" target="_blank">'.getusername($result['uid']).'</a>',
        $_G['setting']['extcredits'][$result['extcredit']]['title'],
        $result['number'].$_G['setting']['extcredits'][$result['extcredit']]['unit'],
        $result['fee'].lang('plugin/'.$pluginName, 'yuan'),
        $result['paytype'],
        $result['state'] == 0 ? '<a href="admin.php?action=plugins&operation=config&identifier='.$pluginName.'&pmod=details&supplement=yes&bill_no='.$result['id'].'&formhash='.formhash().'">'.lang('plugin/'.$pluginName, 'lang51').'</a>':'-'
    ));
}

showtablerow('', array('colspan="99"'), array($multipage));
showtablefooter();
showformfooter();
function getusername ($id){
    return DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = '{$id}' ");
}

?>