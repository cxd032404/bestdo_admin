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
	protected $oUser;
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
            //获取登录方式列表
		    $LoginSourceList = $this->oUserInfo->getLoginSourceList();
			//获取性别列表
			$SexList = $this->oUserInfo->getSexList();
            //获取实名认证状态列表
			$AuthStatusList = $this->oUserInfo->getAuthStatus();
            //获取实名认证证件类型列表
			$AuthIdTypesList = $this->oUserInfo->getAuthIdType();
			//页面参数预处理
			$params['Sex'] = isset($SexList[intval($this->request->Sex??-1)])?intval($this->request->Sex):-1;
			$params['Name'] = urldecode(trim($this->request->Name))?substr(urldecode(trim($this->request->Name)),0,8):"";
			$params['NickName'] = urldecode(trim($this->request->NickName))?substr(urldecode(trim($this->request->NickName)),0,8):"";
			$params['AuthStatus'] = isset($AuthStatusList[$this->request->AuthStatus??-1])?intval($this->request->AuthStatus):-1;
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
			foreach($UserList['UserList'] as $UserId => $UserInfo)
			{
			    //用户性别
				$UserList['UserList'][$UserId]['Sex'] = isset($SexList[$UserInfo['Sex']])?$SexList[$UserInfo['Sex']]:"保密";
                /*
                //实名认证状态
                if(isset($AuthStatusList[$UserInfo['AuthStatus']]))
                {
                    if($UserInfo['AuthStatus'] == 2 && isset($AuthIdTypesList[intval($UserInfo['IdType'])]))
                    {
                        //如果当前已经认证，则同时拼接上认证的证件类型
                        $UserList['UserList'][$UserId]['AuthStatus'] = $AuthStatusList[$UserInfo['AuthStatus']]."/".$AuthIdTypesList[intval($UserInfo['IdType'])];
                    }
                    else
                    {
                        $UserList['UserList'][$UserId]['AuthStatus'] = $AuthStatusList[$UserInfo['AuthStatus']];
                    }
                }
                */
                //实名认证状态
				//$UserList['UserList'][$UserId]['AuthStatus'] = isset($AuthStatusList[$UserInfo['AuthStatus']])?$AuthStatusList[$UserInfo['AuthStatus']]:"未知";
				//$UserList['UserList'][$UserId]['AuthStatus'] = ($UserInfo['auth_state'] == 2 && isset($AuthIdTypesList[intval($UserInfo['id_type'])]))?$UserList['UserList'][$UserId]['AuthStatus']."/".$AuthIdTypesList[intval($UserInfo['id_type'])]:$UserList['UserList'][$UserId]['AuthStatus'];
				//用户生日
				$UserList['UserList'][$UserId]['Birthday'] = is_null($UserInfo['Birthday'])?"未知":$UserInfo['Birthday'];
                $UserList['UserList'][$UserId]['LoginSourceName'] = isset($LoginSourceList[$UserInfo['LastLoginSource']])?$LoginSourceList[$UserInfo['LastLoginSource']]:"未知";
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
		//检查权限ƒ
		$PermissionCheck = $this->manager->checkMenuPermission("UserListDownload");
		if($PermissionCheck['return'])
		{
            //获取登录方式列表
            $LoginSourceList = $this->oUserInfo->getLoginSourceList();
            //获取性别列表
            $SexList = $this->oUserInfo->getSexList();
            //获取实名认证状态列表
            $AuthStatusList = $this->oUserInfo->getAuthStatus();
            //获取实名认证证件类型列表
            $AuthIdTypesList = $this->oUserInfo->getAuthIdType();
			//页面参数预处理
			$params['Sex'] = isset($SexList[intval($this->request->Sex)])?intval($this->request->Sex):-1;
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
				foreach($UserList['UserList'] as $UserId => $UserInfo)
				{
					//生成单行数据
					$t = array();
					$t['UserId'] = $UserInfo['UserId'];
					$t['Name'] = $UserInfo['Name'];
					$t['NickName'] = $UserInfo['NickName'];
					$t['Sex'] = isset($SexList[$UserInfo['Sex']])?$SexList[$UserInfo['Sex']]:"保密";
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
			$SexList = $this->oUserInfo->getSexList();
			//获取实名认证状态列表
			$AuthStatusList = $this->oUserInfo->getAuthStatus();
			//获取实名认证证件类型列表
			$AuthIdTypesList = $this->oUserInfo->getAuthIdType();
			$UserId = trim($this->request->UserId);
			//获取用户信息
			$UserInfo = $this->oUserInfo->getUser($UserId);
			//用户性别
			$UserInfo['Sex'] = isset($SexList[$UserInfo['Sex']])?$SexList[$UserInfo['Sex']]:"保密";
			//实名认证状态
			$UserInfo['AuthStatus'] = isset($AuthStatusList[$UserInfo['AuthStatus']])?$AuthStatusList[$UserInfo['AuthStatus']]:"未知";
			//证件生日
			$UserInfo['Birthday'] = !is_null($UserInfo['Birthday'])?$UserInfo['Birthday']:"未知";
			//用户头像
			$UserInfo['UserImg'] = urldecode($UserInfo['UserImg']);
			//实名认证证件类型
			$UserInfo['AuthIdType'] = isset($AuthIdTypesList[intval($UserInfo['IdType'])])?$AuthIdTypesList[intval($UserInfo['IdType'])]:"未知";
			//获取用户实名认证记录
			//$UserInfo['UserAuthLog'] = $this->oUser->getUserAuthLog($UserId,'submit_time,op_time,op_uid,auth_result,auth_resp');
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
}
