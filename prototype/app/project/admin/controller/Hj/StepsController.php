<?php
/**
 * 俱乐部管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_StepsController extends AbstractController
{
	/**俱乐部:Acitvity
	 * @var string
	 */
	protected $sign = '?ctl=hj/Steps';
    protected $ctl = 'hj/Steps';

    /**
	 * game对象
	 * @var object
	 */
	protected $oSteps;
	protected $oCompany;
    protected $oUserInfo;
    protected $oDepartment;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSteps = new Hj_Steps();
        $this->oDepartment = new Hj_Department();
        $this->oCompany = new Hj_Company();
        $this->oUserInfo = new Hj_UserInfo();

    }
	//步数详情页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//企业ID
			$params['company_id'] = intval($this->request->company_id??0);
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            //三级部门ID
            $params['department_id_3'] = intval($this->request->department_id_3??0);
            //用户姓名
            $params['user_name']= trim($this->requrest->user_name??"");
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            //获取步数详情列表
			$StepsDetailList = $this->oSteps->getStepsDetailList($params);
            $userList  = $goalList = $departmentList = [];

            $spepsConfig = $this->config->steps;
            foreach($StepsDetailList['DetailList'] as $key => $detail)
            {
                if(!isset($userList[$detail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($detail['user_id'],'user_id,true_name,
                    department_id_1,department_id_2,department_id_3');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$detail['user_id']] = $userInfo;
                    }
                }
                $StepsDetailList['DetailList'][$key]['user_name'] = $userList[$detail['user_id']]['true_name']??"未知用户";
                $StepsDetailList['DetailList'][$key]['kcal'] = intval($detail['step']/$this->config->steps['stepsPerKcal']);
                $StepsDetailList['DetailList'][$key]['time'] = intval($detail['step']/$this->config->steps['stepsPerMinute']);
                $StepsDetailList['DetailList'][$key]['distance'] = intval($this->config->steps['distancePerStep']*$detail['step']);
                $StepsDetailList['DetailList'][$key]['company_name'] = $companyList[$detail['company_id']]['company_name']??"未知企业";
                $departmentName = [];
                if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
                {
                    $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                    $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                }
                $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"];
                if($userInfo['department_id_2'] >0)
                {
                    if(!isset($departmentList[$UserInfo['company_id']][$UserInfo['department_id_2']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_2']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_2']] = $departmentInfo;
                    }
                    $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_2']]["department_name"];
                }
                if($userInfo['department_id_3'] >0)
                {
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_3']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_3']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_3']] = $departmentInfo;
                    }
                    $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_3']]["department_name"];
                }
                $StepsDetailList['DetailList'][$key]['department_name'] = implode("|",$departmentName);
                if(!isset($goalList[$detail['company_id']]))
                {
                    $companyDetail = json_decode($companyList[$detail['company_id']]['detail'],true);
                    $goalList[$detail['company_id']] = $companyDetail['daily_step']??5000;
                }
                $StepsDetailList['DetailList'][$key]['achive'] = $detail['step']>=$goalList[$detail['company_id']]?1:0;
                $StepsDetailList['DetailList'][$key]['achive_rate'] = intval(100*($detail['step']/$goalList[$detail['company_id']]));
            }
			//翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'index',$params)."&Page=~page~";
            $page_content =  base_common::multi($StepsDetailList['LogCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //导出EXCEL链接
            $export_var = "<a href =".(Base_Common::getUrl('',$this->ctl,'steps.download',$params))."><导出表格></a>";

			//渲染模版
			include $this->tpl('Hj_Steps_UserDetailList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //步数详情页面
    public function stepsDownloadAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            //三级部门ID
            $params['department_id_3'] = intval($this->request->department_id_3??0);
            //用户姓名
            $params['user_name']= trim($this->requrest->user_name??"");
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
            //分页参数
            $params['PageSize'] = 500;
            $params['getCount'] = 1;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];

            $oExcel = new Third_Excel();
            $FileName= ($this->manager->name().'步数详情');
            $oExcel->download($FileName)->addSheet('每日步数详情');
            //标题栏
            $title = array("企业","部门","姓名","日期","步数","热量","估测时间","估测距离","达标率","是否达标","更新时间");
            $oExcel->addRows(array($title));
            $Count = 1;$params['Page'] =1;
            $userList  = $goalList = [];
            $spepsConfig = $this->config->steps;
            do{
//获取步数详情列表
                $StepsDetailList = $this->oSteps->getStepsDetailList($params);
                $Count = count($StepsDetailList['LogCount']);
                foreach($StepsDetailList['DetailList'] as $key => $detail)
                {
                    if(!isset($userList[$detail['user_id']]))
                    {
                        $userInfo = $this->oUserInfo->getUser($detail['user_id'],'user_id,true_name,department_id_1,department_id_2,department_id_3');
                        if(isset($userInfo['user_id']))
                        {
                            $userList[$detail['user_id']] = $userInfo;
                        }
                    }
                    $StepsDetailList['DetailList'][$key]['user_name'] = $userList[$detail['user_id']]['true_name']??"未知用户";
                    $StepsDetailList['DetailList'][$key]['kcal'] = intval($detail['step']/$this->config->steps['stepsPerKcal']);
                    $StepsDetailList['DetailList'][$key]['time'] = intval($detail['step']/$this->config->steps['stepsPerMinute']);
                    $StepsDetailList['DetailList'][$key]['distance'] = intval($this->config->steps['distancePerStep']*$detail['step']);
                    $StepsDetailList['DetailList'][$key]['company_name'] = $companyList[$detail['company_id']]['company_name']??"未知企业";
                    $departmentName = [];
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                    }
                    $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"];
                    if($userInfo['department_id_2'] >0)
                    {
                        if(!isset($departmentList[$UserInfo['company_id']][$UserInfo['department_id_2']]))
                        {
                            $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_2']);
                            $departmentList[$userInfo['company_id']][$userInfo['department_id_2']] = $departmentInfo;
                        }
                        $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_2']]["department_name"];
                    }
                    if($userInfo['department_id_3'] >0)
                    {
                        if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_3']]))
                        {
                            $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_3']);
                            $departmentList[$userInfo['company_id']][$userInfo['department_id_3']] = $departmentInfo;
                        }
                        $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_3']]["department_name"];
                    }
                    $StepsDetailList['DetailList'][$key]['department_name'] = implode("|",$departmentName);
                    if(!isset($goalList[$detail['company_id']]))
                    {
                        $companyDetail = json_decode($companyList[$detail['company_id']]['detail'],true);
                        $goalList[$detail['company_id']] = $companyDetail['daily_step']??5000;
                    }
                    $StepsDetailList['DetailList'][$key]['achive'] = $detail['step']>=$goalList[$detail['company_id']]?1:0;
                    $StepsDetailList['DetailList'][$key]['achive_rate'] = intval(100*($detail['step']/$goalList[$detail['company_id']]));
                    //生成单行数据
                    $t = array();
                    $t['companyName'] = $StepsDetailList['DetailList'][$key]['company_name'];
                    $t['departmentName'] = $StepsDetailList['DetailList'][$key]['department_name'];
                    $t['userName'] = $StepsDetailList['DetailList'][$key]['user_name'];
                    $t['date'] = $StepsDetailList['DetailList'][$key]['date'];
                    $t['step'] = $StepsDetailList['DetailList'][$key]['step'];
                    $t['kcal'] = $StepsDetailList['DetailList'][$key]['kcal']."kcal";
                    $t['time'] = $StepsDetailList['DetailList'][$key]['time']."分钟";
                    $t['distance'] = $StepsDetailList['DetailList'][$key]['distance']."米";
                    $t['achiveRate'] = $StepsDetailList['DetailList'][$key]['achive_rate']."%";
                    $t['achive'] = $StepsDetailList['DetailList'][$key]['achive']==1?"达标":"未达标";
                    $t['updateTime'] = $StepsDetailList['DetailList'][$key]['update_time'];
                    $oExcel->addRows(array($t));
                    unset($t);
                }
                $params['Page']++;
                $oExcel->closeSheet()->close();
            }
            while($Count>0);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //个人步数统计详情页面
    public function statAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            //三级部门ID
            $params['department_id_3'] = intval($this->request->department_id_3??0);
            //用户姓名
            $params['user_name']= trim($this->requrest->user_name??"");
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            //获取步数统计列表
            $StepsStatList = $this->oSteps->getStepsStatList($params);
            $userList  = $goalList = [];
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            $spepsConfig = $this->config->steps;
            foreach($StepsStatList['List'] as $key => $detail)
            {
                if(!isset($userList[$detail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($detail['user_id'],'user_id,true_name,company_id,
                    department_id_1,department_id_2,department_id_3');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$detail['user_id']] = $userInfo;
                    }
                }
                $StepsStatList['List'][$key]['user_name'] = $userList[$detail['user_id']]['true_name']??"未知用户";
                $StepsStatList['List'][$key]['kcal'] = intval($detail['totalStep']/$spepsConfig['stepsPerKcal']);
                $StepsStatList['List'][$key]['time'] = intval($detail['totalStep']/$spepsConfig['stepsPerMinute']);
                $StepsStatList['List'][$key]['distance'] = intval($spepsConfig['distancePerStep']*$detail['totalStep']);
                $StepsStatList['List'][$key]['company_name'] = $companyList[$userList[$detail['user_id']]['company_id']]['company_name']??"未知企业";
                if(!isset($goalList[$userList[$detail['user_id']]['company_id']]))
                {
                    $companyDetail = json_decode($companyList[$userList[$detail['user_id']]['company_id']]['detail'],true);
                    $goalList[$userList[$detail['user_id']]['company_id']] = ($companyDetail['daily_step']??5000)*$days;
                }
                $departmentName = [];
                if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
                {
                    $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                    $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                }
                $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"];
                if($userInfo['department_id_2'] >0)
                {
                    if(!isset($departmentList[$UserInfo['company_id']][$UserInfo['department_id_2']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_2']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_2']] = $departmentInfo;
                    }
                    $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_2']]["department_name"];
                }
                if($userInfo['department_id_3'] >0)
                {
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_3']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_3']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_3']] = $departmentInfo;
                    }
                    $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_3']]["department_name"];
                }
                $StepsStatList['List'][$key]['department_name'] = implode("|",$departmentName);
                $StepsStatList['List'][$key]['achive'] = $detail['totalStep']>=$goalList[$userList[$detail['user_id']]['company_id']]?1:0;
                $StepsStatList['List'][$key]['achive_rate'] = intval(100*($detail['totalStep']/$goalList[$userList[$detail['user_id']]['company_id']]));
            }
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'stat',$params)."&Page=~page~";
            $page_content =  base_common::multi($StepsStatList['Count'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //导出EXCEL链接
            $export_var = "<a href =".(Base_Common::getUrl('',$this->ctl,'stat.download',$params))."><导出表格></a>";
            //渲染模版
            include $this->tpl('Hj_Steps_Stat');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //步数详情页面
    public function statDownloadAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            //三级部门ID
            $params['department_id_3'] = intval($this->request->department_id_3??0);
            //用户姓名
            $params['user_name']= trim($this->requrest->user_name??"");
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
            //分页参数
            $params['PageSize'] = 500;
            $params['getCount'] = 1;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList([],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];

            $oExcel = new Third_Excel();
            $FileName= ($this->manager->name().'步数详情');
            $oExcel->download($FileName)->addSheet('步数统计');
            //标题栏
            $title = array("企业","部门","姓名","步数","热量","估测时间","估测距离","达标率","是否达标");
            $oExcel->addRows(array($title));
            $Count = 1;$params['Page'] =1;
            $userList  = $goalList = [];
            $spepsConfig = $this->config->steps;
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            do{
//获取步数详情列表
                $StepsStatList = $this->oSteps->getStepsStatList($params);
                $Count = count($StepsStatList['UserCount']);
                foreach($StepsStatList['List'] as $key => $detail)
                {
                    if(!isset($userList[$detail['user_id']]))
                    {
                        $userInfo = $this->oUserInfo->getUser($detail['user_id'],'user_id,true_name,company_id,
                    department_id_1,department_id_2,department_id_3');
                        if(isset($userInfo['user_id']))
                        {
                            $userList[$detail['user_id']] = $userInfo;
                        }
                    }
                    $StepsStatList['List'][$key]['user_name'] = $userList[$detail['user_id']]['true_name']??"未知用户";
                    $StepsStatList['List'][$key]['kcal'] = intval($detail['totalStep']/$spepsConfig['stepsPerKcal']);
                    $StepsStatList['List'][$key]['time'] = intval($detail['totalStep']/$spepsConfig['stepsPerMinute']);
                    $StepsStatList['List'][$key]['distance'] = intval($spepsConfig['distancePerStep']*$detail['totalStep']);
                    $StepsStatList['List'][$key]['company_name'] = $companyList[$userList[$detail['user_id']]['company_id']]['company_name']??"未知企业";
                    if(!isset($goalList[$userList[$detail['user_id']]['company_id']]))
                    {
                        $companyDetail = json_decode($companyList[$userList[$detail['user_id']]['company_id']]['detail'],true);
                        $goalList[$userList[$detail['user_id']]['company_id']] = ($companyDetail['daily_step']??5000)*$days;
                    }
                    $departmentName = [];
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                    }
                    $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"];
                    if($userInfo['department_id_2'] >0)
                    {
                        if(!isset($departmentList[$UserInfo['company_id']][$UserInfo['department_id_2']]))
                        {
                            $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_2']);
                            $departmentList[$userInfo['company_id']][$userInfo['department_id_2']] = $departmentInfo;
                        }
                        $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_2']]["department_name"];
                    }
                    if($userInfo['department_id_3'] >0)
                    {
                        if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_3']]))
                        {
                            $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_3']);
                            $departmentList[$userInfo['company_id']][$userInfo['department_id_3']] = $departmentInfo;
                        }
                        $departmentName[] = $departmentList[$userInfo['company_id']][$userInfo['department_id_3']]["department_name"];
                    }
                    $StepsStatList['List'][$key]['department_name'] = implode("|",$departmentName);
                    $StepsStatList['List'][$key]['achive'] = $detail['totalStep']>=$goalList[$userList[$detail['user_id']]['company_id']]?1:0;
                    $StepsStatList['List'][$key]['achive_rate'] = intval(100*($detail['totalStep']/$goalList[$userList[$detail['user_id']]['company_id']]));
                    //生成单行数据
                    $t = array();
                    $t['companyName'] = $StepsStatList['List'][$key]['company_name'];
                    $t['departmentName'] = $StepsStatList['List'][$key]['department_name'];
                    $t['userName'] = $StepsStatList['List'][$key]['user_name'];
                    $t['step'] = $StepsStatList['List'][$key]['totalStep'];
                    $t['kcal'] = $StepsStatList['List'][$key]['kcal']."kcal";
                    $t['time'] = $StepsStatList['List'][$key]['time']."分钟";
                    $t['distance'] = $StepsStatList['List'][$key]['distance']."米";
                    $t['achiveRate'] = $StepsStatList['List'][$key]['achive_rate']."%";
                    $t['achive'] = $StepsStatList['List'][$key]['achive']==1?"达标":"未达标";
                    $oExcel->addRows(array($t));
                    unset($t);
                }
                $params['Page']++;
                $oExcel->closeSheet()->close();
            }
            while($Count>0);
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //团队步数统计详情页面
    public function departmentStatAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            if($params['department_id_1']>0)
            {
                if($params['department_id_2']>0)
                {
                    $current_level = 3;
                }
                else
                {
                    $current_level = 2;
                }
            }
            else
            {
                $current_level = 1;
            }
            $groupKey = "department_id_".$current_level;
            //用户姓名
            $params['user_name']= trim($this->requrest->user_name??"");
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            $params['PermissionList'] = $totalPermission;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $default_company = array_column($companyList,'company_id')['0'];
            //企业ID
            $params['company_id'] = intval($this->request->company_id??$companyList);


            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            //获取步数统计列表
            $StepsStatList = $this->oSteps->getStepsStatList($params,$groupKey);
            $departmentList  = [];
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            $spepsConfig = $this->config->steps;
            $companyInfo = $companyList[$params['company_id']];
            $companyDetail =
            json_decode($companyList[$params['company_id']]['detail'],true);
            $goal = $companyDetail['daily_step']??6000*$days;
            foreach($StepsStatList['List'] as $key => $detail)
            {
                if(!isset($departmentList[$detail[$groupKey]]))
                {
                    $departmentInfo = $this->oDepartment->getDepartment($detail[$groupKey],'department_name,department_id');
                    if(isset($departmentInfo["department_id"]))
                    {
                        $departmentInfo[$detail[$groupKey]] = $departmentInfo;
                    }
                }
                $StepsStatList['List'][$key]['department_name'] = $departmentInfo[$detail[$groupKey]]['department_name']??"未知部门";
                $StepsStatList['List'][$key]['kcal'] = intval($detail['totalStep']/$spepsConfig['stepsPerKcal']);
                $StepsStatList['List'][$key]['time'] = intval($detail['totalStep']/$spepsConfig['stepsPerMinute']);
                $StepsStatList['List'][$key]['distance'] = intval($spepsConfig['distancePerStep']*$detail['totalStep']);
                $StepsStatList['List'][$key]['achive'] = $detail['totalStep']>=$goal*$detail['userCount']?1:0;
                $StepsStatList['List'][$key]['achive_rate'] = intval(100*($detail['totalStep']/($goal*$detail['userCount'])));
            }
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'stat',$params)."&Page=~page~";
            $page_content =  base_common::multi($StepsStatList['Count'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //导出EXCEL链接
            $export_var = "<a href =".(Base_Common::getUrl('',$this->ctl,'stat.download',$params))."><导出表格></a>";
            //渲染模版
            include $this->tpl('Hj_Steps_DepartmentStat');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
