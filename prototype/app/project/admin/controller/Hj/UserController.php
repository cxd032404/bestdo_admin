<?php
/**用户管理*/

class Hj_UserController extends AbstractController
{
	/**用户管理相关:User
	 * @var string
	 */
	protected $sign = '?ctl=hj/user';
    protected $ctl = 'hj/user';

    /**
	 * game对象
	 * @var object
	 */
	protected $oUserInfo;
    protected $oDepartment;
    protected $oManager;

        /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
        $this->oUserInfo = new Hj_UserInfo();
        $this->oDepartment = new Hj_Department();

    }
	//用户列表
	public function indexAction()
	{
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
		    $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
		    //获取企业列表
            $companyList = (new Hj_Company())->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            //获取登录方式列表
		    $LoginSourceList = $this->oUserInfo->getLoginSourceList();
		    //获取性别列表
			$sexList = $this->oUserInfo->getsexList();
			//页面参数预处理
            $params['company_id'] = $this->request->company_id??0;
            $params['mobile'] = urldecode(trim($this->request->mobile))?substr(urldecode(trim($this->request->mobile)),0,11):"";
            $params['sex'] = isset($sexList[intval($this->request->sex??-1)])?intval($this->request->sex):-1;
			$params['true_name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,20):"";
			$params['nick_name'] = urldecode(trim($this->request->nick_name))?substr(urldecode(trim($this->request->nick_name)),0,20):"";
			//分页参数
			$params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
			$params['PageSize'] = 20;
			//获取用户列表时需要获得记录总数
			$params['getCount'] = 1;
			//获取用户列表
            $UserList = $this->oUserInfo->getUserList(array_merge($params,["permissionList"=>$totalPermission]));
			//导出EXCEL链接
			$export_var = "<a class = 'pb_btn_light_1' href =".(Base_Common::getUrl('',$this->ctl,'user.list.download',$params)).">导出表格</a>";
			//翻页参数
			$page_url = Base_Common::getUrl('',$this->ctl,'index',$params)."&Page=~page~";
			$page_content =  base_common::multi($UserList['UserCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
			$departmentList = [];
			foreach($UserList['UserList'] as $userId => $userInfo)
			{
                $departmentName = [];
			    //用户性别
				$UserList['UserList'][$userId]['sex'] = isset($sexList[$userInfo['sex']])?$sexList[$userInfo['sex']]:"保密";
				//用户生日
				$UserList['UserList'][$userId]['birthday'] = is_null($userInfo['birthday'])?"未知":$userInfo['birthday'];
                $UserList['UserList'][$userId]['LoginSourceName'] = isset($LoginSourceList[$userInfo['last_login_source']])?$LoginSourceList[$userInfo['last_login_source']]:"未知";
                $UserList['UserList'][$userId]['company_name'] = isset($companyList[$userInfo['company_id']])?$companyList[$userInfo['company_id']]['company_name']:"未知";
                if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]) && $userInfo['department_id_1']>0)
                {
                    $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                    $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                    $departmentName = [$departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"]];
                }
                if($userInfo['department_id_2'] >0)
                {
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_2']]))
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
                $UserList['UserList'][$userId]['department_name'] = (count($departmentName)>0 || strlen(implode("_",$departmentName))>0)?implode("_",$departmentName):"未定义";
			}
			//模板渲染
			include $this->tpl('Hj_User_UserList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//用户列表下载
	public function userListDownloadAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("UserListDownload",$this->sign);
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
            $companyList = (new Hj_Company())->getCompanyList([],"company_id,company_name");
		    //获取登录方式列表
            $LoginSourceList = $this->oUserInfo->getLoginSourceList();
            //获取性别列表
            $sexList = $this->oUserInfo->getsexList();
            //获取实名认证状态列表
            $AuthStatusList = $this->oUserInfo->getAuthStatus();
            //获取实名认证证件类型列表
            $AuthIdTypesList = $this->oUserInfo->getAuthIdType();
			//页面参数预处理
            $params['sex'] = isset($sexList[intval($this->request->sex??-1)])?intval($this->request->sex):-1;
            $params['true_name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,8):"";
            $params['nick_name'] = urldecode(trim($this->request->nick_name))?substr(urldecode(trim($this->request->nick_name)),0,8):"";
            $params['mobile'] = urldecode(trim($this->request->mobile))?substr(urldecode(trim($this->request->mobile)),0,11):"";
			//分页参数
			$params['PageSize'] = 500;

            $oExcel = new PHPExcel();

            $oExcel->setActiveSheetIndex(0);           //设置sheet的起始位置
            $oExcel->getActiveSheet()->setTitle('用户列表');      //设置sheet的名称

            //标题栏
			$title = array("用户ID","企业","部门","真实姓名","昵称","联系电话","性别","注册时间","最后登录时间","最后登录方式");
			$startColumn = "A";
			$j = $startColumn;
			$startRow = 1;
			$i = $startRow;
			foreach($title as $value)
			{
			    $oExcel->getActiveSheet()->setCellValue($j.$i,$value);
                $j++;
			}
			$Count = 1;$params['Page'] =1;
            $departmentList = [];
            do
			{
			    $UserList = $this->oUserInfo->getUserList(array_merge($params,["permissionList"=>$totalPermission]));
				$Count = count($UserList['UserList']);
				foreach($UserList['UserList'] as $userId => $userInfo)
				{
                    $i++;$j=$startColumn;
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                    }
                    $departmentName = [$departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"]];
                    if($userInfo['department_id_2'] >0)
                    {
                        if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_2']]))
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
                    $UserList['UserList'][$userId]['department_name'] = implode("_",$departmentName);
				    //生成单行数据
                    $oExcel->getActiveSheet()->setCellValue($j.$i,$userInfo['user_id']);$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,isset($companyList[$userInfo['company_id']])?$companyList[$userInfo['company_id']]['company_name']:"未知");$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,implode("_",$departmentName));$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,$userInfo['true_name']);$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,$userInfo['nick_name']);$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,$userInfo['mobile']);$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,isset($sexList[$userInfo['sex']])?$sexList[$userInfo['sex']]:"保密");$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,$userInfo['reg_time']);$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,$userInfo['last_login_time']);$j++;
                    $oExcel->getActiveSheet()->setCellValue($j.$i,isset($LoginSourceList[$userInfo['last_login_source']])?$LoginSourceList[$userInfo['last_login_source']]:"未知");$j++;
				}
				$params['Page']++;
			}
			while($Count>0);
            $objWriter = PHPExcel_IOFactory::createWriter($oExcel, 'Excel2007');   //通过PHPExcel_IOFactory的写函数将上面数据

            $outputFileName = "用户列表.xls";
            ob_end_clean();
            header("Content-Type: application/force-download");//标头您的浏览器并告诉它下载，而不是在浏览器中运行的文件
            header("Content-Type: application/octet-stream");//文件流
            header("Content-Type: application/download"); //下载文件
            header('Content-Disposition:attachment;filename='.$outputFileName);  //到文件
            header("Content-Transfer-Encoding: binary");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");//上一次修改时间
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache"); //不缓存页面
            $objWriter->save('php://output');
            die();
        }
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //用户列表下载
    public function userListDownloadActionOld()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("UserListDownload",$this->sign);
        if($PermissionCheck['return'])
        {
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
            $companyList = (new Hj_Company())->getCompanyList([],"company_id,company_name");
            //获取登录方式列表
            $LoginSourceList = $this->oUserInfo->getLoginSourceList();
            //获取性别列表
            $sexList = $this->oUserInfo->getsexList();
            //获取实名认证状态列表
            $AuthStatusList = $this->oUserInfo->getAuthStatus();
            //获取实名认证证件类型列表
            $AuthIdTypesList = $this->oUserInfo->getAuthIdType();
            //页面参数预处理
            $params['sex'] = isset($sexList[intval($this->request->sex??-1)])?intval($this->request->sex):-1;
            $params['true_name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,8):"";
            $params['nick_name'] = urldecode(trim($this->request->nick_name))?substr(urldecode(trim($this->request->nick_name)),0,8):"";
            $params['mobile'] = urldecode(trim($this->request->mobile))?substr(urldecode(trim($this->request->mobile)),0,11):"";
            //分页参数
            $params['PageSize'] = 500;



            $oExcel = new Third_Excel();
            $FileName= (iconv('gbk','utf-8','用户列表'));
            $oExcel->download($FileName)->addSheet('用户');

            //标题栏
            $title = array("用户ID","企业","部门","真实姓名","昵称","联系电话","性别","注册时间","最后登录时间","最后登录方式");
            $oExcel->addRows(array($title));

            $Count = 1;$params['Page'] =1;
            $departmentList = [];
            do
            {
                $UserList = $this->oUserInfo->getUserList(array_merge($params,["permissionList"=>$totalPermission]));
                $Count = count($UserList['UserList']);
                foreach($UserList['UserList'] as $userId => $userInfo)
                {
                    if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
                    {
                        $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                        $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
                    }
                    $departmentName = [$departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"]];
                    if($userInfo['department_id_2'] >0)
                    {
                        if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_2']]))
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
                    $UserList['UserList'][$userId]['department_name'] = implode("_",$departmentName);
                    //生成单行数据
                    $t = array();
                    $t['UserId'] = $userInfo['user_id'];
                    $t['companyName'] = isset($companyList[$userInfo['company_id']])?$companyList[$userInfo['company_id']]['company_name']:"未知";
                    $t['departmentName'] = implode("_",$departmentName);
                    $t['TrueName'] = $userInfo['true_name'];
                    $t['NickName'] = $userInfo['nick_name'];
                    $t['Mobile'] = $userInfo['mobile'];
                    $t['sex'] = isset($sexList[$userInfo['sex']])?$sexList[$userInfo['sex']]:"保密";
                    $t['RegTime'] = $userInfo['reg_time'];
                    $t['LastLoginTime'] = $userInfo['last_login_time'];
                    $t['LoginSourceName'] = isset($LoginSourceList[$userInfo['last_login_source']])?$LoginSourceList[$userInfo['last_login_source']]:"未知";
                    $oExcel->addRows(array($t));
                    unset($t);
                }
                $params['Page']++;
            }
            while($Count>0);
            $oExcel->closeSheet()->close();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
	//用户详情
	public function userDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("UserListDownload",$this->sign);
		if($PermissionCheck['return'])
		{
			//获取性别列表
			$sexList = $this->oUserInfo->getsexList();
            //获取登录方式列表
            $LoginSourceList = $this->oUserInfo->getLoginSourceList();
			$userId = trim($this->request->user_id);
			//获取用户信息
			$userInfo = $this->oUserInfo->getUser($userId);
			//用户性别
			$userInfo['sex'] = isset($sexList[$userInfo['sex']])?$sexList[$userInfo['sex']]:"保密";
            $userInfo['LoginSourceName'] = isset($LoginSourceList[$userInfo['last_login_source']])?$LoginSourceList[$userInfo['last_login_source']]:"未知";
            if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_1']]))
            {
                $departmentInfo = $this->oDepartment->getDepartment($userInfo['department_id_1']);
                $departmentList[$userInfo['company_id']][$userInfo['department_id_1']] = $departmentInfo;
            }
            $departmentName = [$departmentList[$userInfo['company_id']][$userInfo['department_id_1']]["department_name"]];
            if($userInfo['department_id_2'] >0)
            {
                if(!isset($departmentList[$userInfo['company_id']][$userInfo['department_id_2']]))
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
            $userInfo['department_name'] = implode("_",$departmentName);
            //渲染模板
			include $this->tpl('Hj_User_UserDetail');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //用户详情
    public function userDepartmentModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("UserUpdate",$this->sign);
        if($PermissionCheck['return'])
        {
            $userId = trim($this->request->user_id);
            //获取用户信息
            $userInfo = $this->oUserInfo->getUser($userId);
            //print_R($userInfo);
            //部门ID
            $department_id= $userInfo['department_id'];
            //获取部门信息
            $departmentInfo = $this->oDepartment->getDepartment($department_id,'*');
            $departmentList_1 = [];
            $departmentList_2 = [];
            $departmentList_3 = [];
            //第一级
            if($departmentInfo['parent_id']==0)
            {
                //第一级的列表获取
                $departmentList_1 = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                $departmentList_1[$userInfo['department_id_1']]['selected'] = 1;
            }
            else
            {
                //获取上级数据
                $parentDepartmentInfo = $this->oDepartment->getDepartment($departmentInfo['parent_id'],'department_name,department_id,parent_id');
                //第一级的列表获取
                $departmentList = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                {
                    //第二级
                    if($parentDepartmentInfo['parent_id']==0)
                    {
                        //第一级的列表获取
                        $departmentList_1 = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                        $departmentList_1[$userInfo['department_id_1']]['selected'] = 1;
                        //第二级的列表获取
                        $departmentList_2 = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>$userInfo['department_id_1']]);
                        $departmentList_2[$userInfo['department_id_2']]['selected'] = 1;
                    }
                    else//第三级别
                    {
                        //第一级的列表获取
                        $departmentList_1 = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>0]);
                        //第二级的列表获取
                        $departmentList_2 = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>$userInfo['department_id_1']]);
                        //第三级的列表获取
                        $departmentList_3 = $this->oDepartment->getDepartmentList(['company_id'=>$departmentInfo['company_id'],'parent_id'=>$userInfo['department_id_2']]);
                        $departmentList_1[$userInfo['department_id_1']]['selected'] = 1;
                        $departmentList_2[$userInfo['department_id_2']]['selected'] = 1;
                        $departmentList_3[$userInfo['department_id_3']]['selected'] = 1;
                    }
                }
            }
            //渲染模板
            include $this->tpl('Hj_User_Department');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //企业导入用户列表
    public function companyUserListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("CompanyUserLoad");
        if($PermissionCheck['return'])
        {
            //获取企业列表
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            $companyList = (new Hj_Company())->getCompanyList(["permissionList"=>$totalPermission],"company_name,company_id");
            $params['company_id'] = $this->request->company_id??0;
            $params['name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,8):"";
            $params['mobile'] = urldecode(trim($this->request->mobile))?substr(urldecode(trim($this->request->mobile)),0,11):"";
            $params['worker_id'] = urldecode(trim($this->request->worker_id))?substr(urldecode(trim($this->request->worker_id)),0,8):"";
            //分页参数
            $params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
            $params['PageSize'] = 20;
            //获取用户列表时需要获得记录总数
            $params['getCount'] = 1;
            //获取用户列表
            $UserList = $this->oUserInfo->getCompanyUserList(array_merge($params,["permissionList"=>$totalPermission]));
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'company.user.list',$params)."&Page=~page~";
            $page_content =  base_common::multi($UserList['UserCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            $oDepartment = new Hj_Department;
            $departmentList = [];
            foreach($UserList['UserList'] as $userId => $userInfo)
            {
                if(!isset($departmentList[$userInfo['department_id']]))
                {
                    $departmentInfo = $oDepartment->getDepartment($userInfo['department_id']);
                    if(isset($departmentInfo['department_id']))
                    {
                        $departmentList[$userInfo['department_id']] = $departmentInfo;
                    }
                }
                $UserList['UserList'][$userId]['company_name'] = isset($companyList[$userInfo['company_id']])?$companyList[$userInfo['company_id']]['company_name']:"未知";
                $UserList['UserList'][$userId]['department_name'] = isset($departmentList[$userInfo['department_id']])?$departmentList[$userInfo['department_id']]['department_name']:"未知";
            }
            //模板渲染
            include $this->tpl('Hj_User_CompanyUserList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //企业上传用户名单的提交页面
    public function companyUserUploadSubmitAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
        if($PermissionCheck['return'])
        {
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            $companyList = (new Hj_Company())->getCompanyList(["permissionList"=>$totalPermission],"company_name,company_id,detail");
            $currentCompany = current($companyList);
            $detail = json_decode($currentCompany['detail'],true);
            $companyUserAuthType = $this->oUserInfo->getCompanyUserAuthType();
            $isset = isset($companyUserAuthType[$detail['authType']??""]);
            //模板渲染
            include $this->tpl('Hj_User_CompanyUserUpload');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //导入企业用户名单
    public function companyUserUploadAction()
    {
        //元素ID
        $company_id = intval($this->request->company_id);
        $auth_type = $this->request->auth_type??"";
        $oCompany = (new Hj_Company());
        $companyInfo = $oCompany->getCompany($company_id,"company_id,detail");
        $detail = json_decode($companyInfo['detail'],true);
        if(!isset($detail['authType']))
        {
            //$detail['authType'] = $auth_type;
            //$detail = json_encode($detail);
            //$oCompany->updateCompany($company_id,['detail'=>$detail]);
        }
        else
        {
            $auth_type = $detail['authType'];
        }
        //没有选择验证方式
        if($auth_type=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            //上传图片
            $oUpload = new Base_Upload('upload_txt');
            $upload = $oUpload->upload('upload_txt');
            $upload = $upload->resultArr;
            if($upload[1]['errno']==0)
            {
                //$index = (new Base_Cache_Elasticsearch())->checkIndex("company_user_list",['company_id'=>$company_id]);
                $file_path = $upload[1]['path'];
                $handle = fopen($file_path, 'r');
                $error = 0;
                $success = 0;
                $exist = 0;
                $successList = [];
                //循环到文件结束
                while(!feof($handle))
                {
                    //获取每行信息
                    $content = fgets($handle, 8080);
                    if(trim($content)!="" && !is_null($content))
                    {
                        $text = explode(",",$content);
                        $t = explode("|",$text[2]);
                        $departmentName = $t[count($t)-1];
                        $departmentInfo = (new Hj_Department())->getDepartmentByName($company_id,$departmentName);
                        if(count($text)>=2)
                        {
                            $existUser = $this->oUserInfo->getCompanyUserByColumn($company_id,trim($text['0']),$auth_type,trim($text[1]),"id,name,department_id");
                            if(count($existUser)>=1)
                            {
                                if($existUser[0]['department_id'] != $departmentInfo['department_id'])
                                {
                                    $this->oUserInfo->updateCompanyUser($existUser[0]['id'],['department_id'=>$departmentInfo['department_id']??0]);
                                }
                                $successList = array_merge($successList,array_column(array_values($existUser),"id"));
                                $exist ++;
                            }
                            else
                            {
                                $userInfo = ["company_id" => $company_id, "name" => trim($text[0]),"department_id" => $departmentInfo['department_id']??0 ,$auth_type => trim($text[1])];
                                $insert = $this->oUserInfo->insertCompanyUser($userInfo);
                                if($insert)
                                {
                                    $successList[] = $insert;
                                    $success++;
                                }
                                else
                                {
                                    $error++;
                                }
                            }
                        }
                        else
                        {
                            $error++;
                        }
                    }
                }
            }
            //$userToEs = $this->oUserInfo->getCompanyUserList(["idList"=>$successList],["id","company_id","name","mobile","worker_id","user_id"])['UserList'];
            //$index = (new Base_Cache_Elasticsearch())->companyUserIndex($userToEs,$this->config->elasticsearch);
            $index = 1;
            $response = array('errno' => 0,'result'=>["success"=>$success,"error"=>$error,"exist"=>$exist,"index"=>$index]);
            if($success>0)
            {
                if(!isset($detail['authType']))
                {
                    $detail['authType'] = $auth_type;
                    $detail = json_encode($detail);
                    $oCompany->updateCompany($company_id,['detail'=>$detail]);
                    Base_Common::refreshCache($this->config,"company",$company_id);
                }
            }
        }
        echo json_encode($response);
        return true;
    }
    //修改页面元素
    public function departmentUpdateAction()
    {
        //用户ID
        $user_id = intval($this->request->user_id);
        //部门ID
        $department['department_id_1'] = intval($this->request->department_id_1);
        $department['department_id_2'] = intval($this->request->department_id_2);
        $department['department_id_3'] = intval($this->request->department_id_3);
        if($department['department_id_3']==0)
        {
            if($department['department_id_2']==0)
            {
                $department['department_id'] = $department['department_id_1'];
            }
            else
            {
                $department['department_id'] = $department['department_id_2'];
            }
        }
        else
        {
            $department['department_id'] = $department['department_id_3'];
        }
        $update = $this->oUserInfo->updateUser($user_id,$department);
        $updateCompanyUser = $this->oUserInfo->updateCompanyUserByUser($user_id,['department_id'=>$department['department_id']]);
        Base_Common::refreshCache($this->config,"user",$user_id);
        unset($department['department_id']);
        $oStep = new Hj_Department();
        $oStep->setUserDepartment($user_id,$department);

        $response = $update ? array('errno' => 0) : array('errno' => 9);
        echo json_encode($response);
        return true;
    }
    //停用用户账户
    public function userDisableAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("UserUpdate",$this->sign);
        if($PermissionCheck['return'])
        {
            //用户ID
            $user_id = intval($this->request->user_id);
            //获取用户信息
            $userInfo = $this->oUserInfo->getUser($user_id);
            if($userInfo['is_del']!=1)
            {
                $update = $this->oUserInfo->updateUser($user_id,['is_del'=>1]);
            }
            //刷新列表
            Base_Common::refreshCache($this->config,"user",$user_id);
            //返回之前的页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //设定用户的测试状态
    public function setTestUserAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("UserUpdate",$this->sign);
        if($PermissionCheck['return'])
        {
            //用户ID
            $user_id = intval($this->request->user_id);
            //测试状态
            $status= intval($this->request->status);
            $user_info = ["test"=>$status>0?1:0];
            $update = $this->oUserInfo->updateUser($user_id,$user_info);
            //刷新列表
            Base_Common::refreshCache($this->config,"user",$user_id);
            //返回之前的页面
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
