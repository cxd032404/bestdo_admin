<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: IndexController.php 15233 2014-08-04 06:46:08Z 334746 $
 */

class IndexController extends AbstractController
{

	/**
	 * 框架页
	 */
    public function indexAction()
    {
    	$oMenu = new Widget_Menu();
    	$oMenuPurview = new Widget_Menu_Permission();
		$allowedMenuArr = $oMenuPurview->getTopPermissionByGroup($this->manager->menu_group_id,0);
        $menuArr = $this->getChildMenu(0);
        $groupInfo = (new Widget_Group())->get($this->manager->menu_group_id);
        include $this->tpl();
    }
    
    /**
	 * 
	 * 递归获取菜单
	 * @author 张骥
	 */
    public function getChildMenu($parentId)
    {
        $Menu = new Widget_Menu();
        $ChildMenu = $Menu->getChildMenu($parentId);
        
        if(count($ChildMenu)){
            foreach($ChildMenu as $key=>$val){
                $rescurTree = $this->getChildMenu($val['menu_id']);
                if(count($rescurTree)){
                    $ChildMenu[$key]['tree'] = $rescurTree;
                }
            }            
        }
        
        return $ChildMenu;
    }

    public function home2Action()
    {
        $oUpdateLog = new Hj_UpdateLog();
        //获取更新记录列表
        $UpdateLogList = $oUpdateLog->getUpdateLogList(1,8);
        //更新记录类型列表
        $UpdateLogTypeList = $oUpdateLog->getLogTypeList();
        include $this->tpl();
    }
    public function home3Action()
    {
        $oCompany = new Hj_Company();
        $oUser = new Hj_UserInfo();
        $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
        //获取企业列表
        $companyList = $oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
        $startDate = date("Y-m-d",time()-7*86400);
        $endDate = date("Y-m-d",time());
        $date = $endDate;
        $regData = [];
        while($date >= $startDate)
        {
            $regData[$date] = $oUser->getUserCount(["permissionList"=>$totalPermission,"regDate"=>$date]);
            $date = date("Y-m-d",strtotime($date)-86400);
        }
        include('Third/fusion/Includes/FusionCharts_Gen.php');
        $FC = new FusionCharts("MSLine",'100%','400');
        # Set the relative path of the swf file
        $FC->setSWFPath( '../Charts/');
        $Step=1;
        # Store chart attributes in a variable
        $strParam="caption='最近7天用户情况';xAxisName='注册';baseFontSize=12;numberPrefix=;numberSuffix=人;decimalPrecision=0;showValues=0;formatNumberScale=0;labelStep=".$Step.";rotateNames=1;yAxisMinValue=0;yAxisMaxValue=100;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
        foreach($regData as $date => $regUser)
        {
            $FC->addCategory($date);
        }
        foreach($regData as $date => $regUser)
        {
            $FC->addChartData($regUser);
        }
        include $this->tpl("Hj_Index_Home");
    }
    public function homeAction()
    {
        $oCompany = new Hj_Company();
        $oActivity = new Hj_Activity();
        $oUser = new Hj_UserInfo();
        $oList = new Hj_List();
        $oPosts = new Hj_Posts();
        $oSteps = new Hj_Steps();
        $oClub = new Hj_Club();
        $oDepartment = new Hj_Department();
        $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
        //获取企业列表
        $companyList = $oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
        $default_company = array_column($companyList,'company_id')['0'];
        //活动报名与上传作品情况分析
        $nameList = [];
        $userCount = [];
        $postCount = [];
        $activityList = $oActivity->getActivityList(["company_id"=>$default_company,'system'=>1,'purchased'=>1],"activity_id,activity_name");
        foreach($activityList['ActivityList'] as $activity_id => $activityInfo)
        {
            {
                $nameList[$activity_id] = "'".$activityInfo['activity_name']."'";
                $userCount[$activity_id] = $oUser->getUserActivityLogCount(["activity_id"=> $activity_id]);
                $ListList = $oList->getListList(["activity_id"=>$activity_id],"list_id");
                $postCount[$activity_id] = $oPosts->getPostCountByList(array_keys($ListList['ListList']));
            }
        }
        $nameListText = implode(",",$nameList);
        $userCountText = implode(",",$userCount);
        $postCountText = implode(",",$postCount);

        //部门步数排行榜+部门步数占比排行榜模块
        //企业ID
        $currentTime = time();
        //开始日期
        $params['start_date']= date("Y-07-01",$currentTime);
        //结束日期
        $params['end_date']= date("Y-m-d",$currentTime);

        $params['company_id'] = $default_company;
        //获取步数统计列表
        $StepsStatList = $oSteps->getStepsStatList($params,"department_id_1");
        $maxSteps = max(array_column($StepsStatList['List'],"totalStep"));
        $totalSteps = array_sum(array_column($StepsStatList['List'],"totalStep"));

        foreach($StepsStatList['List'] as $department_id => $detail)
        {
            $departmentInfo = $oDepartment->getDepartment($department_id,'department_name,department_id');
            $StepsStatList['List'][$department_id]['department_name'] = $departmentInfo['department_name']??"未知部门";
            $StepsStatList['List'][$department_id]['bar_rate'] = sprintf("%10.2f",$detail['totalStep']/$maxSteps*100);
            $StepsStatList['List'][$department_id]['circle_rate'] = sprintf("%10.2f",$detail['totalStep']/$totalSteps*100);
        }
        //俱乐部活动分析模块
        $clubList = $oActivity->getActivityCountListByClub(["company_id"=>$default_company,"Page"=>1,"PageSize"=>10]);
        foreach($clubList as $club_id => $activityInfo)
        {
            $clubInfo = $oClub->getClub($club_id,"club_id,club_name");
            $clubList[$club_id]['club_name'] = $clubInfo['club_name']??"未知俱乐部";
            $activityList = $oActivity->getActivityList(["club_id"=>$club_id],"activity_id");
            $clubList[$club_id]['user_count']= $oUser->getUserActivityLogCount(['activity_id'=>array_keys($activityList['ActivityList'])]);
        }
        //顶部数据模块
        $totalUserCount = $oUser->getUsercount(['company_id'=>$default_company]);
        $currentDate = date("Y-m-d");
        $monthStartDate = date("Y-07-01");
        $totalStep_today = $oSteps->getStepsSum(['company_id'=>$default_company,"start_date"=>$currentDate])??0;
        $totalStep_month = $oSteps->getStepsSum(['company_id'=>$default_company,"start_date"=>$monthStartDate])??0;
        $totalStep_total = $oSteps->getStepsSum(['company_id'=>$default_company])??0;
        $companyInfo = $oCompany->getCompany($default_company,"company_id,detail");
        $companyInfo['detail'] = json_decode($companyInfo['detail'],true);
        $dailyStep = $companyInfo['detail']['daily_step']??6000;
        $achive_rate_month = $totalStep_month>0?sprintf("%10.2f" ,intval(date("t")) * $totalUserCount * $dailyStep / $totalStep_month * 100):0;
        include $this->tpl("Index_home3");
    }
}