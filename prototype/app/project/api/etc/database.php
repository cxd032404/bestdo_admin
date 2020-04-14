<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: database.php 1362 2010-01-17 11:00:03Z 闄堟檽涓?$
 */
include dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))."/CommonConfig/databaseConfig.php";

$db = array();
$db['isPersistent'] = 0;
$db['xrace'][0] = array(
	'host' => HOST_LOCAL,
	'user' => USER_LOCAL,
	'password' => PASSWORD_LOCAL,
	'port' => PORT_LOCAL,
	'database' => 'xrace',
);

$db['xrace_config'][0] = array(
	'host' => HOST_LOCAL,
	'user' => USER_LOCAL,
	'password' => PASSWORD_LOCAL,
	'port' => PORT_LOCAL,
	'database' => 'xrace_config',
);

$db['xrace_bm'][0] = array(
	'host' => HOST_LOCAL,
	'user' => USER_LOCAL,
	'password' => PASSWORD_LOCAL,
	'port' => PORT_LOCAL,
	'database' => 'xrace_bm',
);

$db['mylaps'][0] = array(
	'host' => HOST_LOCAL,
	'user' => USER_LOCAL,
	'password' => PASSWORD_LOCAL,
	'port' => PORT_LOCAL,
	'database' => 'mylaps',
);

$db['wechat_timing'][0] = array(
    'host' => HOST_LOCAL,
    'user' => USER_LOCAL,
    'password' => PASSWORD_LOCAL,
    'port' => PORT_LOCAL,
    'database' => 'wechat_timing',
);

$db['mylapstest'][0] = array(
    'host' => HOST_LOCAL,
    'user' => USER_LOCAL,
    'password' => PASSWORD_LOCAL,
    'port' => PORT_LOCAL,
    'database' => 'mylapstest',
);

$db['xrace_user'][0] = array(
	'host' => HOST_LOCAL,
	'user' => USER_LOCAL,
	'password' => PASSWORD_LOCAL,
	'port' => PORT_LOCAL,
	'database' => 'xrace_user',
);

$db['xrace_credit'][0] = array(
	'host' => HOST_LOCAL,
	'user' => USER_LOCAL,
	'password' => PASSWORD_LOCAL,
	'port' => PORT_LOCAL,
	'database' => 'xrace_credit',
);

$db['xrace_race'][0] = array(
    'host' => HOST_LOCAL,
    'user' => USER_LOCAL,
    'password' => PASSWORD_LOCAL,
    'port' => PORT_LOCAL,
    'database' => 'xrace_race',
);
$db['qcode'][0] = array(
    'host' => HOST_LOCAL,
    'user' => USER_LOCAL,
    'password' => PASSWORD_LOCAL,
    'port' => PORT_LOCAL,
    'database' => 'qcode',
);
return $db;
?>
