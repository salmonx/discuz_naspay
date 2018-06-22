<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS `cdb_naspay_order`;
CREATE TABLE `cdb_naspay_order` (
  `id` char(32) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `number` int(11) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `creation_time` int(11) NOT NULL,
  `finish_time` int(11) NOT NULL,
  `uid` mediumint(8) NOT NULL,
  `extcredit` tinyint(4) NOT NULL,
  `paytype` char(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM;

EOF;

runquery($sql);
		
$finish = TRUE;

?>