<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

DB::query("DROP TABLE IF EXISTS ".DB::table('cdb_naspay_order')."");

$finish = TRUE;