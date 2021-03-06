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
            $params['true_name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,20):"";
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            //三级部门ID
            $params['department_id_3'] = intval($this->request->department_id_3??0);
            //开始日期
            $params['start_date']= $this->request->start_date??date("Y-m-d",time()-3*86400);
            //结束日期
            $params['end_date']= $this->request->end_date??date("Y-m-d");
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");

            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            if($params['true_name'] != "")
            {
                //获取用户列表
                $UserList = $this->oUserInfo->getUserList(array_merge(['true_name'=>$params['true_name']],["permissionList"=>$totalPermission]),["user_id"]);
            }
            else
            {
                $UserList = ['UserList'=>[]];
            }
            //获取步数详情列表
			$StepsDetailList = $this->oSteps->getStepsDetailList(array_merge($params,["permissionList"=>$totalPermission],['user_id'=>array_column($UserList['UserList'],"user_id")]));
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
                $StepsDetailList['DetailList'][$key]['department_name'] = implode("_",$departmentName);
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
            $export_var = "<a class = 'pb_btn_light_1' href =".(Base_Common::getUrl('',$this->ctl,'steps.download',$params)).">导出表格</a>";

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
        $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
        if($PermissionCheck['return'])
        {
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
            $params['true_name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,20):"";
            //一级部门ID
            $params['department_id_1'] = intval($this->request->department_id_1??0);
            //二级部门ID
            $params['department_id_2'] = intval($this->request->department_id_2??0);
            //三级部门ID
            $params['department_id_3'] = intval($this->request->department_id_3??0);
            //用户姓名
            $params['user_name']= trim($this->request->user_name??"");
            //开始日期
            $params['start_date']= trim($this->request->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->request->end_date??date("Y-m-d"));
            //分页参数
            $params['PageSize'] = 500;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            if($params['true_name'] != "")
            {
                //获取用户列表
                $UserList = $this->oUserInfo->getUserList(array_merge(['true_name'=>$params['true_name']],["permissionList"=>$totalPermission]),["user_id"]);
            }
            else
            {
                $UserList = ['UserList'=>[]];
            }

            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setactivesheetindex(0);
            /** 设置工作表名称 */
            $objPHPExcel->getActiveSheet(0)->setTitle('步数详情');
            $objPHPExcel->getActiveSheet(0)
                ->setCellValue('A1', '企业')
                ->setCellValue('B1', '部门')
                ->setCellValue('C1', '姓名')
                ->setCellValue('D1', '日期')
                ->setCellValue('E1', '步数')
                ->setCellValue('F1', '热量')
                ->setCellValue('G1', '估测时间')
                ->setCellValue('H1', '估测距离')
                ->setCellValue('I1', '达标率')
                ->setCellValue('J1', '是否达标')
                ->setCellValue('K1', '更新时间');

            $Count = 1;$params['Page'] =1;
            $userList  = $goalList = [];
            $spepsConfig = $this->config->steps;

            do{
                //获取步数详情列表
                $StepsDetailList = $this->oSteps->getStepsDetailList(array_merge($params,["permissionList"=>$totalPermission],['user_id'=>array_column($UserList['UserList'],"user_id")]));
                $Count = count($StepsDetailList['DetailList']);

                $row = $params['PageSize']*($params['Page']-1)+2;
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
                    $StepsDetailList['DetailList'][$key]['department_name'] = implode("_",$departmentName);
                    if(!isset($goalList[$detail['company_id']]))
                    {
                        $companyDetail = json_decode($companyList[$detail['company_id']]['detail'],true);
                        $goalList[$detail['company_id']] = $companyDetail['daily_step']??5000;
                    }
                    $StepsDetailList['DetailList'][$key]['achive'] = $detail['step']>=$goalList[$detail['company_id']]?1:0;
                    $StepsDetailList['DetailList'][$key]['achive_rate'] = intval(100*($detail['step']/$goalList[$detail['company_id']]));
                    //生成单行数据
                    $objPHPExcel->getActiveSheet(0)
                        ->setCellValue('A'.$row,$StepsDetailList['DetailList'][$key]['company_name'])
                        ->setCellValue('B'.$row,$StepsDetailList['DetailList'][$key]['department_name'])
                        ->setCellValue('C'.$row,$StepsDetailList['DetailList'][$key]['user_name'])
                        ->setCellValue('D'.$row,$StepsDetailList['DetailList'][$key]['date'])
                        ->setCellValue('E'.$row,$StepsDetailList['DetailList'][$key]['step'])
                        ->setCellValue('F'.$row,$StepsDetailList['DetailList'][$key]['kcal']."kcal")
                        ->setCellValue('G'.$row,$StepsDetailList['DetailList'][$key]['time']."分钟")
                        ->setCellValue('H'.$row,$StepsDetailList['DetailList'][$key]['distance']."米")
                        ->setCellValue('I'.$row,$StepsDetailList['DetailList'][$key]['achive_rate']."%")
                        ->setCellValue('J'.$row,$StepsDetailList['DetailList'][$key]['achive']==1?"达标":"未达标")
                        ->setCellValue('K'.$row,$StepsDetailList['DetailList'][$key]['update_time']);
                    $row++;
                }
                $params['Page']++;
            }
            while($Count>0);
            $objPHPExcel->setactivesheetindex(0);
            ob_end_clean();
            @header('pragma:public');
            @header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.'步数详情'.'.xls"');
            @header("Content-Disposition:attachment;filename=步数详情.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
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
            $params['user_name']= trim($this->request->user_name??"");
            //开始日期
            $params['start_date']= trim($this->request->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->request->end_date??date("Y-m-d"));
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            //获取步数统计列表
            $StepsStatList = $this->oSteps->getStepsStatList(array_merge($params,["permissionList"=>$totalPermission]));
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
                $StepsStatList['List'][$key]['department_name'] = implode("_",$departmentName);
                $StepsStatList['List'][$key]['achive'] = $detail['totalStep']>=$goalList[$userList[$detail['user_id']]['company_id']]?1:0;
                $StepsStatList['List'][$key]['achive_rate'] = intval(100*($detail['totalStep']/$goalList[$userList[$detail['user_id']]['company_id']]));
            }
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'stat',$params)."&Page=~page~";
            $page_content =  base_common::multi($StepsStatList['Count'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //导出EXCEL链接
            $export_var = "<a class = 'pb_btn_light_1' href =".(Base_Common::getUrl('',$this->ctl,'stat.download',$params)).">导出表格</a>";
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
        $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
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
            $params['user_name']= trim($this->request->user_name??"");
            //开始日期
            $params['start_date']= trim($this->request->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->request->end_date??date("Y-m-d"));
            //分页参数
            $params['PageSize'] = 500;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];


            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setactivesheetindex(0);
            /** 设置工作表名称 */
            $objPHPExcel->getActiveSheet(0)->setTitle('步数统计');
            $objPHPExcel->getActiveSheet(0)
                ->setCellValue('A1', '企业')
                ->setCellValue('B1', '部门')
                ->setCellValue('C1', '姓名')
                ->setCellValue('D1', '步数')
                ->setCellValue('E1', '热量')
                ->setCellValue('F1', '估测时间')
                ->setCellValue('G1', '估测距离')
                ->setCellValue('H1', '达标率')
                ->setCellValue('I1', '是否达标');


            $Count = 1;$params['Page'] =1;
            $userList  = $goalList = [];
            $spepsConfig = $this->config->steps;
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            do{
                //获取步数详情列表
                $StepsStatList = $this->oSteps->getStepsStatList(array_merge($params,["permissionList"=>$totalPermission]));
                $Count = count($StepsStatList['List']);

                $row = $params['PageSize']*($params['Page']-1)+2; //行下标

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
                    $StepsStatList['List'][$key]['department_name'] = implode("_",$departmentName);
                    $StepsStatList['List'][$key]['achive'] = $detail['totalStep']>=$goalList[$userList[$detail['user_id']]['company_id']]?1:0;
                    $StepsStatList['List'][$key]['achive_rate'] = intval(100*($detail['totalStep']/$goalList[$userList[$detail['user_id']]['company_id']]));
                    //生成单行数据
                    $objPHPExcel->getActiveSheet(0)
                        ->setCellValue('A'.$row,$StepsStatList['List'][$key]['company_name'])
                        ->setCellValue('B'.$row,$StepsStatList['List'][$key]['department_name'])
                        ->setCellValue('C'.$row,$StepsStatList['List'][$key]['user_name'])
                        ->setCellValue('D'.$row,$StepsStatList['List'][$key]['totalStep'])
                        ->setCellValue('E'.$row,$StepsStatList['List'][$key]['kcal']."kcal")
                        ->setCellValue('F'.$row,$StepsStatList['List'][$key]['time']."分钟")
                        ->setCellValue('G'.$row,$StepsStatList['List'][$key]['distance']."米")
                        ->setCellValue('H'.$row,$StepsStatList['List'][$key]['achive_rate']."%")
                        ->setCellValue('I'.$row,$StepsStatList['List'][$key]['achive']==1?"达标":"未达标");
                    $row++;
                }
                $params['Page']++;
            }
            while($Count>0);
            $objPHPExcel->setactivesheetindex(0);
            ob_end_clean();
            @header('pragma:public');
            @header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.'步数统计'.'.xls"');
            @header("Content-Disposition:attachment;filename=步数统计.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
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
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
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
            $params['user_name']= trim($this->request->user_name??"");
            //开始日期
            $params['start_date']= trim($this->request->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->request->end_date??date("Y-m-d"));
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            $params['PermissionList'] = $totalPermission;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $default_company = array_column($companyList,'company_id')['0'];
            //企业ID
            $params['company_id'] = intval($this->request->company_id??$default_company);


            $departmentList_1 = $params['company_id']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>0],"department_id,department_name"):[];
            $departmentList_2 = $params['department_id_1']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_1']],"department_id,department_name"):[];
            $departmentList_3 = $params['department_id_2']>0?$this->oDepartment->getDepartmentList(["company_id"=>$params['company_id'],"parent_id"=>$params['department_id_2']],"department_id,department_name"):[];
            //获取步数统计列表
            $StepsStatList = $this->oSteps->getStepsStatList(array_merge($params,["permissionList"=>$totalPermission]),$groupKey);
            $departmentList  = [];
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            $spepsConfig = $this->config->steps;
            $companyInfo = $companyList[$params['company_id']];
            $companyDetail =
            json_decode($companyInfo['detail'],true);
            $goal = ($companyDetail['daily_step']??6000)*$days;
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
                $StepsStatList['List'][$key]['department_name'] = $departmentInfo[$detail[$groupKey]]['department_name']??"直属人员";
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
            $export_var = "<a class = 'pb_btn_light_1' href =".(Base_Common::getUrl('',$this->ctl,'department.download',$params)).">导出表格</a>";
            //渲染模版
            include $this->tpl('Hj_Steps_DepartmentStat');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //步数详情页面
    public function departmentDownloadAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
        if($PermissionCheck['return'])
        {
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
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
            $params['user_name']= trim($this->request->user_name??"");
            //开始日期
            $params['start_date']= trim($this->request->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->request->end_date??date("Y-m-d"));
            //分页参数
            $params['PageSize'] = 500;
            $params['getCount'] = 1;
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            $params['PermissionList'] = $totalPermission;
            //获取企业列表
            $companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name,detail");
            $default_company = array_column($companyList,'company_id')['0'];
            //企业ID
            $params['company_id'] = intval($this->request->company_id??$default_company);


            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setactivesheetindex(0);
            /** 设置工作表名称 */
            $objPHPExcel->getActiveSheet(0)->setTitle('部门达成率统计');
            $objPHPExcel->getActiveSheet(0)
                ->setCellValue('A1', '企业')
                ->setCellValue('B1', '部门')
                ->setCellValue('C1', '步数')
                ->setCellValue('D1', '热量')
                ->setCellValue('E1', '估测时间')
                ->setCellValue('F1', '估测距离')
                ->setCellValue('G1', '达标率')
                ->setCellValue('H1', '是否达标');


            $Count = 1;$params['Page'] =1;
            $departmentList  = [];
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            $spepsConfig = $this->config->steps;
            $companyInfo = $this->oCompany->getCompany($params['company_id'],"company_id,company_name,detail");
            $companyDetail =
                json_decode($companyInfo['detail'],true);
            $goal = ($companyDetail['daily_step']??6000)*$days;
            do{
                //获取步数详情列表
                $StepsStatList = $this->oSteps->getStepsStatList(array_merge($params,["permissionList"=>$totalPermission]),$groupKey);
                $Count = count($StepsStatList['List']);

                $row = $params['PageSize']*($params['Page']-1)+2; //行下标
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
                    $StepsStatList['List'][$key]['department_name'] = $departmentInfo[$detail[$groupKey]]['department_name']??"直属人员";
                    $StepsStatList['List'][$key]['kcal'] = intval($detail['totalStep']/$spepsConfig['stepsPerKcal']);
                    $StepsStatList['List'][$key]['time'] = intval($detail['totalStep']/$spepsConfig['stepsPerMinute']);
                    $StepsStatList['List'][$key]['distance'] = intval($spepsConfig['distancePerStep']*$detail['totalStep']);
                    $StepsStatList['List'][$key]['achive'] = $detail['totalStep']>=$goal*$detail['userCount']?1:0;
                    $StepsStatList['List'][$key]['achive_rate'] = intval(100*($detail['totalStep']/($goal*$detail['userCount'])));

                    //生成单行数据
                    $objPHPExcel->getActiveSheet(0)
                        ->setCellValue('A'.$row,$companyInfo['company_name'])
                        ->setCellValue('B'.$row,$StepsStatList['List'][$key]['department_name'])
                        ->setCellValue('C'.$row,$StepsStatList['List'][$key]['totalStep'])
                        ->setCellValue('D'.$row,$StepsStatList['List'][$key]['kcal']."kcal")
                        ->setCellValue('E'.$row,$StepsStatList['List'][$key]['time']."分钟")
                        ->setCellValue('F'.$row,$StepsStatList['List'][$key]['distance']."米")
                        ->setCellValue('G'.$row,$StepsStatList['List'][$key]['achive_rate']."%")
                        ->setCellValue('H'.$row,$StepsStatList['List'][$key]['achive']==1?"达标":"未达标");
                    $row++;
                }
                $params['Page']++;
            }
            while($Count>0);
            $objPHPExcel->setactivesheetindex(0);
            ob_end_clean();
            @header('pragma:public');
            @header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.'部门达成率统计'.'.xls"');
            @header("Content-Disposition:attachment;filename=部门达成率统计.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
