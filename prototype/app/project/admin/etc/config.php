<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: config.php 15195 2014-07-23 07:18:26Z 334746 $
 */
$file = __APP_ROOT_DIR__."../../../../CommonConfig/keyConfig.php";
$keyConfig = require $file;
$file = __APP_ROOT_DIR__."../../../../CommonConfig/urlConfig.php";
$urlConfig = require $file;
$config = array();
$config['steps'] = [
    'stepsPerKcal' => 20,
    'distancePerStep' => 0.6,
    'stepsPerMinute' => 60];
$config['js'] = '/js/';
$config['style'] = '/style/';
$config['companyName'] = "文野体育";
$config['companyUrl'] = $urlConfig['companyUrl'];
$config['projectName'] = "文体之家控制台";
$config['currentVersion'] = "2.0";
$config['api'] = [
    'api'=>[
        'post'=>'/list/post/',
        'source_remove'=>'/list/source_remove',
        'get_page'=>'/page/getPage/',
        'get_token_for_manager'=>'/user/createTokenForManager',
        'display'=>'/list/post_display',
        'refresh'=>'/cache/refresh',
        'invite_club'=>'/club/inviteToClub',
        'leave_club'=>'/club/leaveClub',
    ],
        'site'=>[
    'company_user_reg'=>'loginhome',
    ]
];
$config['apiUrl'] = $urlConfig['apiUrl'];
$config['siteUrl'] = $urlConfig['siteUrl'];
$config['adminUrl'] = $urlConfig['adminUrl'];
$config['aliConfig'] = $keyConfig['aliyun'];

$config['oss'] = array_merge($config['aliConfig'],
    [
        // 'END_POINT'=>'oss-cn-shanghai.aliyuncs.com',
        // 'BUCKET'=>'xrace-pic'
         'END_POINT'=>'oss-cn-shanghai.aliyuncs.com',
         'BUCKET'=>'fu-company-home'
    ]);
$config['sms'] = array_merge($config['aliConfig'],
    [
        "template"=>["reg"=>'SMS_187936155'],
        "signName"=>"易赛admin短信",
        "regionId"=>"cn-hangzhou"
    ]);
$config['alipay'] = ['appid'=>'2021001156616661'];
$config['elasticsearch'] = [
    "company_user_list" => ["index"=>"company_user_list","type"=>"company_user_list"],
    "question_list" => ["index"=>"question_list","type"=>"question_list"]

];
return $config;
