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
    public function homeAction()
    {
        $oCompany = new Hj_Company();
        $oUser = new Hj_UserInfo();
        $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
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
}