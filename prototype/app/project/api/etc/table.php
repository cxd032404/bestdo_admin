<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: table.php 15195 2014-07-23 07:18:26Z 334746 $
 */

$table = array();

#用户积分变更记录
$table['user_credit_log_total']['db'] = 'xrace_user';
$table['user_credit_log_total']['num'] = 1;

#用户积分汇总表
$table['user_credit_sum']['db'] = 'xrace_user';
$table['user_credit_sum']['num'] = 1;

#用户积分变更记录
$table['user_credit_log']['db'] = 'xrace_user';
$table['user_credit_log']['num'] = 1;

#积分类目
$table['config_credit']['db'] = 'xrace_config';
$table['config_credit']['num'] = 1;

#动作
$table['config_action']['db'] = 'xrace_config';
$table['config_action']['num'] = 1;

#系统更新记录
$table['UploadLog']['db'] = 'xrace_config';
$table['UploadLog']['num'] = 1;

#用户信息
$table['UserInfo']['db'] = 'xrace_user';
$table['UserInfo']['num'] = 1;

#比赛用户信息
$table['RaceUserInfo']['db'] = 'xrace_user';
$table['RaceUserInfo']['num'] = 1;

#用户注册
$table['UserReg']['db'] = 'xrace_user';
$table['UserReg']['num'] = 1;

#用户注册
$table['UserRegLog']['db'] = 'xrace_user';
$table['UserRegLog']['num'] = 1;

#用户重置密码
$table['UserResetPassword']['db'] = 'xrace_user';
$table['UserResetPassword']['num'] = 1;

#用户重置密码日志
$table['UserResetPasswordLog']['db'] = 'xrace_user';
$table['UserResetPasswordLog']['num'] = 1;

#用户的成员列表
$table['UserMemberList']['db'] = 'xrace_user';
$table['UserMemberList']['num'] = 1;

#用户审核状态
$table['user_auth']['db'] = 'xrace';
$table['user_auth']['num'] = 1;

#用户审核记录
$table['user_auth_log']['db'] = 'xrace';
$table['user_auth_log']['num'] = 1;

#用户信息
$table['user_profile']['db'] = 'xrace';
$table['user_profile']['num'] = 1;

#计时点
$table['config_race']['db'] = 'xrace_config';
$table['config_race']['num'] = 1;

#计时点
$table['config_timing_point']['db'] = 'xrace_config';
$table['config_timing_point']['num'] = 1;

#运动类型
$table['config_sports_type']['db'] = 'xrace_config';
$table['config_sports_type']['num'] = 1;

#赛事
$table['config_race_catalog']['db'] = 'xrace_config';
$table['config_race_catalog']['num'] = 1;

#比赛类型
$table['config_race_type']['db'] = 'xrace_config';
$table['config_race_type']['num'] = 1;

#商品类型
$table['config_product_type']['db'] = 'xrace_config';
$table['config_product_type']['num'] = 1;

#商品
$table['config_product']['db'] = 'xrace_config';
$table['config_product']['num'] = 1;

#赛事分站
$table['config_race_stage']['db'] = 'xrace_config';
$table['config_race_stage']['num'] = 1;

#赛事分组
$table['config_race_group']['db'] = 'xrace_config';
$table['config_race_group']['num'] = 1;

#管理员
$table['config_manager']['db'] = 'rc_config';
$table['config_manager']['num'] = 1;

#管理员组
$table['config_group']['db'] = 'xrace_config';
$table['config_group']['num'] = 1;

#菜单
$table['config_menu']['db'] = 'xrace_config';
$table['config_menu']['num'] = 1;

#菜单权限
$table['config_menu_purview']['db'] = 'xrace_config';
$table['config_menu_purview']['num'] = 1;

#菜单权限
$table['config_menu_permission']['db'] = 'xrace_config';
$table['config_menu_permission']['num'] = 1;

//操作日志
$table['config_logs_manager']['db'] = 'xrace_config';
$table['config_logs_manager']['num'] = 16;

//APP版本
$table['config_app_version']['db'] = 'xrace_config';
$table['config_app_version']['num'] = 1;

//APP系统
$table['config_app_os']['db'] = 'xrace_config';
$table['config_app_os']['num'] = 1;

//APP类型
$table['config_app_type']['db'] = 'xrace_config';
$table['config_app_type']['num'] = 1;

//用户执照
$table['user_license']['db'] = 'xrace';
$table['user_license']['num'] = 1;

//报名记录（选手名单）
$table['user_race']['db'] = 'xrace';
$table['user_race']['num'] = 1;

//队伍列表
$table['race_team']['db'] = 'xrace';
$table['race_team']['num'] = 1;

//用户参加队伍列表
$table['user_team']['db'] = 'xrace';
$table['user_team']['num'] = 1;

//订单记录表
$table['hs_order']['db'] = 'xrace_bm';
$table['hs_order']['num'] = 1;

//mylaps计时数据入口表
$table['times']['db'] = 'mylaps';
$table['times']['num'] = 1;

//微信打卡计时数据入口表
$table['wechat_times']['db'] = 'qcode';
$table['wechat_times']['num'] = 1;

//mylaps打卡计时数据入口表
$table['times_sorted']['db'] = 'mylaps';
$table['times_sorted']['num'] = 1;

#签到记录
$table['user_stage_checkin']['db'] = 'xrace';
$table['user_stage_checkin']['num'] = 1;

//登录Token表
$table['UserLogin']['db'] = 'xrace_user';
$table['UserLogin']['num'] = 1;

#用户验证列表
$table['UserAuthCode']['db'] = 'xrace_user';
$table['UserAuthCode']['num'] = 1;

#用户验证日志
$table['UserAuthCodeLog']['db'] = 'xrace_user';
$table['UserAuthCodeLog']['num'] = 1;

#搜索关键字
$table['config_search_keyword']['db'] = 'xrace_config';
$table['config_search_keyword']['num'] = 1;

#用户芯片列表
$table['ChipList']['db'] = 'xrace_user';
$table['ChipList']['num'] = 1;

#场地
$table['config_arena']['db'] = 'rc_config';
$table['config_arena']['num'] = 1;

#约战队列
$table['RaceApplyQueue']['db'] = 'xrace_race';
$table['RaceApplyQueue']['num'] = 1;

#约战队列
$table['AppliedRaceList']['db'] = 'xrace_race';
$table['AppliedRaceList']['num'] = 1;

#用户参赛信息
$table['UserRaceList']['db'] = 'xrace_race';
$table['UserRaceList']['num'] = 1;

//排名列表
$table['config_total_ranking']['db'] = 'xrace_config';
$table['config_total_ranking']['num'] = 1;

//排名对应的比赛列表
$table['config_ranking_race']['db'] = 'xrace_config';
$table['config_ranking_race']['num'] = 1;

#计时点赛段
$table['config_race_segment']['db'] = 'xrace_config';
$table['config_race_segment']['num'] = 1;

return $table;
