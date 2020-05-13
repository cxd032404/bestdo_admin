<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: config.php 15195 2014-07-23 07:18:26Z 334746 $
 */
$file = __APP_ROOT_DIR__."../../../../CommonConfig/keyConfig.php";
$keyConfig = require $file;
$config = array();
$config['js'] = '/js/';
$config['style'] = '/style/';
$config['companyName'] = "合伽体育";
$config['companyUrl'] = "http://www.bestdo.com";
$config['projectName'] = "Bestdo控制台";
$config['currentVersion'] = "1.0";
$config['api'] = ['root'=>"http://api.bestdo.cn",
    'list'=>[
        'post'=>'/list/post/',
        'source_remove'=>'/list/source_remove',
                'get_page'=>'/oage/get_page/'
    ]


];

$config['adminUrl'] = "http://admin.bestdo.cn";
$config['aliConfig'] = $keyConfig['aliyun'];
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
$config['alipay'] = ['appid'=>'2021001156616661'];
$config['elasticsearch'] = [
    "company_user_list" => ["index"=>"company_user_list","type"=>"company_user_list"]
];
return $config;
