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
	}
	//用户列表
	public function indexAction()
	{
	    //检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
            //获取企业列表
            $companyList = (new Hj_Company())->getCompanyList([],"company_id,company_name");
            //获取登录方式列表
		    $LoginSourceList = $this->oUserInfo->getLoginSourceList();
		    //获取性别列表
			$sexList = $this->oUserInfo->getsexList();
			//页面参数预处理
            $params['company_id'] = $this->request->company_id??0;
            $params['sex'] = isset($sexList[intval($this->request->sex??-1)])?intval($this->request->sex):-1;
			$params['true_name'] = urldecode(trim($this->request->true_name))?substr(urldecode(trim($this->request->true_name)),0,8):"";
			$params['nick_name'] = urldecode(trim($this->request->nick_name))?substr(urldecode(trim($this->request->nick_name)),0,8):"";
			//分页参数
			$params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
			$params['PageSize'] = 20;
			//获取用户列表时需要获得记录总数
			$params['getCount'] = 1;
			//获取用户列表
			$UserList = $this->oUserInfo->getUserList($params);
			//导出EXCEL链接
			$export_var = "<a href =".(Base_Common::getUrl('',$this->ctl,'user.list.download',$params))."><导出表格></a>";
			//翻页参数
			$page_url = Base_Common::getUrl('',$this->ctl,'index',$params)."&Page=~page~";
			$page_content =  base_common::multi($UserList['UserCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
			foreach($UserList['UserList'] as $userId => $UserInfo)
			{
			    //用户性别
				$UserList['UserList'][$userId]['sex'] = isset($sexList[$UserInfo['sex']])?$sexList[$UserInfo['sex']]:"保密";
				//用户生日
				$UserList['UserList'][$userId]['birthday'] = is_null($UserInfo['birthday'])?"未知":$UserInfo['birthday'];
                $UserList['UserList'][$userId]['LoginSourceName'] = isset($LoginSourceList[$UserInfo['last_login_source']])?$LoginSourceList[$UserInfo['last_login_source']]:"未知";
                $UserList['UserList'][$userId]['company_name'] = isset($companyList[$UserInfo['company_id']])?$companyList[$UserInfo['company_id']]['company_name']:"未知";

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
		$PermissionCheck = $this->manager->checkMenuPermission("UserListDownload");
		if($PermissionCheck['return'])
		{
            //获取登录方式列表
            $LoginSourceList = $this->oUserInfo->getLoginSourceList();
            //获取性别列表
            $sexList = $this->oUserInfo->getsexList();
            //获取实名认证状态列表
            $AuthStatusList = $this->oUserInfo->getAuthStatus();
            //获取实名认证证件类型列表
            $AuthIdTypesList = $this->oUserInfo->getAuthIdType();
			//页面参数预处理
			$params['sex'] = isset($sexList[intval($this->request->sex)])?intval($this->request->sex):-1;
			$params['Name'] = urldecode(trim($this->request->Name))?substr(urldecode(trim($this->request->Name)),0,8):"";
			$params['NickName'] = urldecode(trim($this->request->NickName))?substr(urldecode(trim($this->request->NickName)),0,8):"";
			$params['AuthStatus'] = isset($AuthStatusList[$this->request->AuthStatus])?intval($this->request->AuthStatus):-1;

			//分页参数
			$params['PageSize'] = 500;

			$oExcel = new Third_Excel();
			$FileName= ($this->manager->name().'用户列表');
			$oExcel->download($FileName)->addSheet('用户');
			//标题栏
			$title = array("用户ID","姓名","昵称","性别","出生年月",/*"实名认证状态",*/"注册时间","最后登录时间","最后登录方式");
			$oExcel->addRows(array($title));
			$Count = 1;$params['Page'] =1;
			do
			{
				$UserList = $this->oUserInfo->getUserList($params);
				$Count = count($UserList['UserList']);
				foreach($UserList['UserList'] as $userId => $UserInfo)
				{
					//生成单行数据
					$t = array();
					$t['UserId'] = $UserInfo['UserId'];
					$t['Name'] = $UserInfo['Name'];
					$t['NickName'] = $UserInfo['NickName'];
					$t['sex'] = isset($sexList[$UserInfo['sex']])?$sexList[$UserInfo['sex']]:"保密";
                    $t['Birthday'] = $UserInfo['Birthday'];

                    //实名认证状态
                    /*
                    if(isset($AuthStatusList[$UserInfo['AuthStatus']]))
                    {
                        if($UserInfo['AuthStatus'] == 2 && isset($AuthIdTypesList[intval($UserInfo['IdType'])]))
                        {
                            //如果当前已经认证，则同时拼接上认证的证件类型
                            $t['AuthStatus'] = $AuthStatusList[$UserInfo['AuthStatus']]."/".$AuthIdTypesList[intval($UserInfo['IdType'])];
                        }
                        else
                        {
                            $t['AuthStatus'] = $AuthStatusList[$UserInfo['AuthStatus']];
                        }
                    }
                    */
                    $t['RegTime'] = $UserInfo['RegTime'];
                    $t['LastLoginTime'] = $UserInfo['LastLoginTime'];
                    $t['LoginSourceName'] = isset($LoginSourceList[$UserInfo['LastLoginSource']])?$LoginSourceList[$UserInfo['LastLoginSource']]:"未知";

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
	//用户详情
	public function userDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("UserListDownload");
		if($PermissionCheck['return'])
		{
			//获取性别列表
			$sexList = $this->oUserInfo->getsexList();
			//获取实名认证状态列表
			$AuthStatusList = $this->oUserInfo->getAuthStatus();
			//获取实名认证证件类型列表
			$AuthIdTypesList = $this->oUserInfo->getAuthIdType();
			$userId = trim($this->request->UserId);
			//获取用户信息
			$UserInfo = $this->oUserInfo->getUser($userId);
			//用户性别
			$UserInfo['sex'] = isset($sexList[$UserInfo['sex']])?$sexList[$UserInfo['sex']]:"保密";
			//实名认证状态
			$UserInfo['AuthStatus'] = isset($AuthStatusList[$UserInfo['AuthStatus']])?$AuthStatusList[$UserInfo['AuthStatus']]:"未知";
			//证件生日
			$UserInfo['Birthday'] = !is_null($UserInfo['Birthday'])?$UserInfo['Birthday']:"未知";
			//用户头像
			$UserInfo['UserImg'] = urldecode($UserInfo['UserImg']);
			//实名认证证件类型
			$UserInfo['AuthIdType'] = isset($AuthIdTypesList[intval($UserInfo['IdType'])])?$AuthIdTypesList[intval($UserInfo['IdType'])]:"未知";
			//获取用户实名认证记录
			//$UserInfo['UserAuthLog'] = $this->oUser->getUserAuthLog($userId,'submit_time,op_time,op_uid,auth_result,auth_resp');
            $UserInfo['UserAuthLog'] = array();
            if(count($UserInfo['UserAuthLog']))
			{
				//初始化一个空的后台管理员列表
				$ManagerList = array();
				//获取实名认证记录的状态列表
				$AuthLogIdStatusList = $this->oUser->getAuthLogStatusTypeList();
				foreach($UserInfo['UserAuthLog'] as $LogId => $AuthLog)
				{
					// 如果管理员记录已经获取到
					if(isset($ManagerList[$AuthLog['op_uid']]))
					{
						$ManagerInfo = $ManagerList[$AuthLog['op_uid']];
					}
					//否则重新获取
					else
					{
						$ManagerInfo = $this->manager->get($AuthLog['op_uid'], "name");
					}
					//记录管理员账号
					$UserInfo['UserAuthLog'][$LogId]['ManagerName'] = $ManagerInfo['name'];
					//认证结果
					$UserInfo['UserAuthLog'][$LogId]['AuthResult'] = $AuthLogIdStatusList[$AuthLog['auth_result']];
				}
			}
			//渲染模板
			include $this->tpl('Hj_User_UserDetail');
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
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //获取企业列表
            $companyList = (new Hj_Company())->getCompanyList([],"company_id,company_name");
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
            $UserList = $this->oUserInfo->getCompanyUserList($params);
            //导出EXCEL链接
            $export_var = "<a href =".(Base_Common::getUrl('',$this->ctl,'user.list.download',$params))."><导出表格></a>";
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'index',$params)."&Page=~page~";
            $page_content =  base_common::multi($UserList['UserCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            foreach($UserList['UserList'] as $userId => $UserInfo)
            {
                $UserList['UserList'][$userId]['company_name'] = isset($companyList[$UserInfo['company_id']])?$companyList[$UserInfo['company_id']]['company_name']:"未知";
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
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            $companyList = (new Hj_Company())->getCompanyList([],"company_name,company_id");
            $companyUserAuthType = $this->oUserInfo->getCompanyUserAuthType();
            //模板渲染
            include $this->tpl('Hj_User_CompanyUserUpload');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改页面元素
    public function companyUserUploadAction()
    {
        //元素ID
        $company_id = intval($this->request->company_id);
        $auth_type = $this->request->auth_type??"";
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
                $index = (new Base_Cache_Elasticsearch())->checkIndex("company_user_list",['company_id'=>$company_id]);
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
                        $text = explode("｜",$content);
                        if(count($text)>=2)
                        {
                            $existUser = $this->oUserInfo->getCompanyUserByColumn($company_id,trim($text['0']),$auth_type,trim($text[1]),"id,name");
                            if(count($existUser)>=1)
                            {
                                $successList = array_merge($successList,array_column(array_values($existUser),"id"));
                                $exist ++;
                            }
                            else
                            {
                                $userInfo = ["company_id" => $company_id, "name" => trim($text[0]), $auth_type => trim($text[1])];
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
            $userToEs = $this->oUserInfo->getCompanyUserList(["idList"=>$successList],["id","company_id","name","mobile","worker_id","user_id"])['UserList'];
            $index = (new Base_Cache_Elasticsearch())->companyUserIndex($userToEs,$this->config->elasticsearch);
            $response = array('errno' => 0,'result'=>["success"=>$success,"error"=>$error,"exist"=>$exist,"index"=>$index]);
        }
        echo json_encode($response);
        return true;
    }
}
