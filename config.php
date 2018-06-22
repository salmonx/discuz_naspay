<?php
$mainurl = 'https://mainnet.nebulas.io/v1/user/call';
$dappaddress="n1e4PAFP3xDGMQ6iNgBUaZYc9JgDWJ43dqi";


function request_post($url,$param) {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
}

function objtoarr($obj){
    $ret = array();
    foreach($obj as $key =>$value){
        if(gettype($value) == 'array' || gettype($value) == 'object'){
            $ret[$key] = objtoarr($value);
        }else{
         $ret[$key] = $value;
        }
    }
    return $ret;
}

?>