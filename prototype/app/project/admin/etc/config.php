<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: config.php 15195 2014-07-23 07:18:26Z 334746 $
 */

$config = array();
$config['js'] = '/js/';
$config['style'] = '/style/';
$config['companyName'] = "合伽体育";
$config['companyUrl'] = "http://www.bestdo.com";
$config['projectName'] = "Bestdo控制台";
$config['currentVersion'] = "1.0";
$config['apiUrl'] = "http://api.bestdo.cn";
$config['adminUrl'] = "http://admin.bestdo.cn";
$config['aliConfig'] = [
    'ACCESS_KEY_ID'=>'LTAI4FkbExDy9cEfwqNfb93X',
    'ACCESS_KEY_SECRET'=>'57iMpXwB0UYR71tXGuacAHmoCDtTaL'
        ];
$config['oss'] = array_merge($config['aliConfig'],
    [
         'END_POINT'=>'oss-cn-shanghai.aliyuncs.com',
         'BUCKET'=>'xrace-pic'

    ]);
$config['sms'] = array_merge($config['aliConfig'],
    [
        "template"=>["reg"=>'SMS_187936155'],
        "signName"=>"易赛admin短信",
        "regionId"=>"cn-hangzhou"
    ]);

return $config;
