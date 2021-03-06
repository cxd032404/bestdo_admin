<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: table.php 15195 2014-07-23 07:18:26Z 334746 $
 */

$table = array();
#搜索关键字
$table['config_search_keyword']['db'] = 'hj_config';
$table['config_search_keyword']['num'] = 1;


#积分类目
$table['config_credit']['db'] = 'hj_config';
$table['config_credit']['num'] = 1;

#系统更新记录
$table['UpdateLog']['db'] = 'hj_config';
$table['UpdateLog']['num'] = 1;

#运动类型
$table['config_sports_type']['db'] = 'hj_config';
$table['config_sports_type']['num'] = 1;

#比赛
$table['config_race']['db'] = 'hj_config';
$table['config_race']['num'] = 1;


#管理员
$table['config_manager']['db'] = 'hj_config';
$table['config_manager']['num'] = 1;

#管理员组
$table['config_group']['db'] = 'hj_config';
$table['config_group']['num'] = 1;

#菜单
$table['config_menu']['db'] = 'hj_config';
$table['config_menu']['num'] = 1;

#菜单权限
$table['config_menu_purview']['db'] = 'hj_config';
$table['config_menu_purview']['num'] = 1;

#菜单权限
$table['config_menu_permission']['db'] = 'hj_config';
$table['config_menu_permission']['num'] = 1;

#数据权限
$table['config_data_permission']['db'] = 'hj_config';
$table['config_data_permission']['num'] = 1;

//操作日志
$table['config_logs_manager']['db'] = 'hj_config';
$table['config_logs_manager']['num'] = 16;

//APP版本
$table['config_app_version']['db'] = 'hj_config';
$table['config_app_version']['num'] = 1;

//APP系统
$table['config_app_os']['db'] = 'hj_config';
$table['config_app_os']['num'] = 1;

//APP类型
$table['config_app_type']['db'] = 'hj_config';
$table['config_app_type']['num'] = 1;


#积分类目
$table['config_credit']['db'] = 'hj_config';
$table['config_credit']['num'] = 1;

#动作
$table['config_action']['db'] = 'hj_config';
$table['config_action']['num'] = 1;

#用户信息状态
$table['user_info']['db'] = 'hj_user';
$table['user_info']['num'] = 1;

#富文本测试表
$table['rich_text_text']['db'] = 'hj_user';
$table['rich_text_text']['num'] = 1;

#企业
$table['config_company']['db'] = 'hj_config';
$table['config_company']['num'] = 1;

#企业对客户端的权限
$table['config_company_access']['db'] = 'hj_config';
$table['config_company_access']['num'] = 1;

#部门
$table['config_department']['db'] = 'hj_config';
$table['config_department']['num'] = 1;

#页面
$table['config_page']['db'] = 'hj_config';
$table['config_page']['num'] = 1;

#页面元素
$table['config_page_element']['db'] = 'hj_config';
$table['config_page_element']['num'] = 1;

#页面元素类型
$table['config_element']['db'] = 'hj_config';
$table['config_element']['num'] = 1;

#活动相关
$table['config_activity']['db'] = 'hj_config';
$table['config_activity']['num'] = 1;

#企业导入的用户列表
$table['company_user_list']['db'] = 'hj_user';
$table['company_user_list']['num'] = 1;

#列表
$table['config_list']['db'] = 'hj_config';
$table['config_list']['num'] = 1;

#文章
$table['user_posts']['db'] = 'hj_user';
$table['user_posts']['num'] = 1;

#投票相关
$table['config_vote']['db'] = 'hj_config';
$table['config_vote']['num'] = 1;

#提问相关
$table['config_question']['db'] = 'hj_config';
$table['config_question']['num'] = 1;

#协议相关
$table['config_protocal']['db'] = 'hj_config';
$table['config_protocal']['num'] = 1;

#活动报名记录
$table['user_activity_log']['db'] = 'hj_user';
$table['user_activity_log']['num'] = 1;

#俱乐部相关
$table['config_club']['db'] = 'hj_config';
$table['config_club']['num'] = 1;

#俱乐部相关
$table['club_member']['db'] = 'hj_user';
$table['club_member']['num'] = 1;

#俱乐部相关
$table['club_member_log']['db'] = 'hj_user';
$table['club_member_log']['num'] = 1;

#步数详情记录
$table['user_steps']['db'] = 'hj_user';
$table['user_steps']['num'] = 1;

#企业自定义的健步走活动时间段
$table['config_steps_date_range']['db'] = 'hj_config';
$table['config_steps_date_range']['num'] = 1;

#系统配置
$table['config_default']['db'] = 'hj_config';
$table['config_default']['num'] = 1;

#资源配置
$table['config_source']['db'] = 'hj_config';
$table['config_source']['num'] = 1;

#活动排名列表
$table['user_activity_list_rank']['db'] = 'hj_user';
$table['user_activity_list_rank']['num'] = 1;

#列表集合表
$table['config_list_series']['db'] = 'hj_config';
$table['config_list_series']['num'] = 1;

#列表集合详情表
$table['config_list_series_detail']['db'] = 'hj_config';
$table['config_list_series_detail']['num'] = 1;

#队伍表
$table['user_team']['db'] = 'hj_user';
$table['user_team']['num'] = 1;

#选手表
$table['user_athlete']['db'] = 'hj_user';
$table['user_athlete']['num'] = 1;

#场地表
$table['config_place']['db'] = 'hj_config';
$table['config_place']['num'] = 1;

#赛程表
$table['user_race_schedual']['db'] = 'hj_user';
$table['user_race_schedual']['num'] = 1;

#赛程表详情表
$table['schedual_vs_detail']['db'] = 'hj_user';
$table['schedual_vs_detail']['num'] = 1;
return $table;
