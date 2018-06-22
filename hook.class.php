<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_nsapay {
	function common(){
		global $_G;
		$setreplace = $_G['cache']['plugin']['naspay']['replace'];
		if($setreplace == 1){
			if($_GET['mod'] == 'spacecp' && $_GET['ac'] == 'credit' && $_GET['op'] == 'buy'){
				$url = 'plugin.php?id=naspay:recharge';
				header("HTTP/1.1 301 Moved Permanently");
				dheader("location: $url");
			}
		}
	}
}

?>