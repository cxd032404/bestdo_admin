<?php
/**
 * 俱乐部管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_ClubController extends AbstractController
{
	/**俱乐部:Acitvity
	 * @var string
	 */
	protected $sign = '?ctl=hj/club';
    protected $ctl = 'hj/club';

    /**
	 * game对象
	 * @var object
	 */
	protected $oClub;
	protected $oCompany;
	protected $oClubMember;
    protected $oUserInfo;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oClub = new Hj_Club();
		$this->oCompany = new Hj_Company();
        $this->oUserInfo = new Hj_UserInfo();
        $this->oClubMember = new Hj_ClubMember();

    }
	//俱乐部配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //企业ID
			$company_id = intval($this->request->company_id??0);
			//获取俱乐部列表
			$clubList = $this->oClub->getClubList(["permissionList"=>$totalPermission,'company_id'=>$company_id]);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
			//循环俱乐部列表
			foreach($clubList as $key => $clubInfo)
            {
                //数据解包
                $clubList[$key]['detail'] = json_decode($clubInfo['detail'],true);
				$clubList[$key]['company_name'] = ($clubInfo['company_id']==0)?"无对应":($companyList[$clubInfo['company_id']]['company_name']??"未知");
                //获取成员数量
				$clubList[$key]['member_count'] = $this->oClubMember->getMemberCount(['club_id'=>$clubInfo['club_id'],'status'=>1])??0;
				//分页参数
                $params['Page'] = 1;
                $params['PageSize'] = 1;
                //获取列表时需要获得记录总数
                $params['getCount'] = 1;
            }
			//渲染模版
			include $this->tpl('Hj_Club_ClubList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加俱乐部类型填写配置俱乐部
	public function clubAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addClub");
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //获取顶级俱乐部列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
			//渲染模版
			include $this->tpl('Hj_Club_ClubAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新俱乐部
	public function clubInsertAction()
	{
		$connectedUser = (new Hj_UserInfo())->getConnectedUserInfo($this->manager->id);
		if(!$connectedUser['user_id'])
        {
            $response = array('errno' => 8);
        }
		else
        {
            //检查权限
            $bind=$this->request->from('club_name','company_id','club_sign','member_limit','allow_enter');
            //俱乐部名称不能为空
            if(trim($bind['club_name'])=="")
            {
                $response = array('errno' => 1);
            }
            else
            {
                if(trim($bind['club_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $clubExists = $this->oClub->getClubList(['company_id'=>$bind['company_id'],'club_sign'=>$bind['club_sign']],'club_id,club_sign');
                    if(count($clubExists)>0)
                    {
                        $response = array('errno' => 4);
                    }
                    else
                    {
                        $oUpload = new Base_Upload('upload_img');
                        $upload = $oUpload->upload('upload_img',$this->config->oss);
                        $oss_urls = array_column($upload->resultArr,'oss');
                        $bind['icon'] = implode("",$oss_urls);
                        if(trim($bind['icon'])=="")
                        {
                            $response = array('errno' => 2);
                        }
                        else
                        {
                            //数据打包
                            $bind['detail'] = json_encode([]);
                            $bind['content'] = "";
                            //添加俱乐部
                            $res = $this->oClub->insertClub($bind);
                            if($res)
                            {
                                Base_Common::refreshCache($this->config,"club",$res);
                                $memberInfo = ['club_id'=>$res,
                                    'company_id'=>$bind['company_id'],
                                    'user_id'=>$connectedUser['user_id'],
                                    'permission'=>9,'status'=>1,
                                    'detail'=>json_encode(['comment'=>"俱乐部创建自动加入"]),];
                                $memberLogInfo = ['club_id'=>$res,
                                    'company_id'=>$bind['company_id'],
                                    'type'=>1,'sub_type'=>1,
                                    'user_id'=>$connectedUser['user_id'],
                                    'operate_user_id'=>$connectedUser['user_id'],
                                    'process_user_id'=>$connectedUser['user_id'],
                                    'detail'=>json_encode(['comment'=>"俱乐部创建自动加入"]),
                                    'result'=>1];
                                //写入成员
                                $member = $this->oClubMember->insertClubMember($memberInfo);
                                //写入成员日志
                                $log = $this->oClubMember->insertClubMemberLog($memberLogInfo);
                            }
                            $response = $res ? array('errno' => 0) : array('errno' => 9);
                        }
                    }
                }
            }
        }

		echo json_encode($response);
		return true;
	}

	//修改俱乐部信息
	public function clubModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateClub");
		if($PermissionCheck['return'])
		{
			//俱乐部ID
			$club_id= intval($this->request->club_id);
			//获取俱乐部信息
			$clubInfo = $this->oClub->getClub($club_id,'*');
			//数据解包
            $clubInfo['detail'] = json_decode($clubInfo['detail'],true);
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups);
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            //渲染模版
			include $this->tpl('Hj_Club_ClubModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //俱乐部banner列表
    public function bannerAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateClub");
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $club_id= intval($this->request->club_id);
            //获取俱乐部信息
            $clubInfo = $this->oClub->getClub($club_id,'*');
            //数据解包
            $clubInfo['detail'] = json_decode($clubInfo['detail'],true);
            //渲染模版
            include $this->tpl('Hj_Club_Banner');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

	//更新俱乐部信息
	public function clubUpdateAction()
	{
        $connectedUser = (new Hj_UserInfo())->getConnectedUserInfo($this->manager->id);
        if(!$connectedUser['user_id'])
        {
            $response = array('errno' => 8);
        }
        else
        {
            //接收俱乐部参数
            $bind=$this->request->from('club_id','club_name','company_id','club_sign','member_limit','allow_enter');
            //俱乐部名称不能为空
            if(trim($bind['club_name'])=="")
            {
                $response = array('errno' => 1);
            }
            else
            {
                if(trim($bind['club_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $clubExists = $this->oClub->getClubList(['company_id'=>$bind['company_id'],'club_sign'=>$bind['club_sign'],'exclude_id'=>$bind['club_id']],'club_id,club_sign');
                    if(count($clubExists)>0)
                    {
                        $response = array('errno' => 4);
                    }
                    else
                    {
                        $oUpload = new Base_Upload('upload_img');
                        $upload = $oUpload->upload('upload_img',$this->config->oss);
                        $oss_urls = array_column($upload->resultArr,'oss');
                        $bind['icon'] = implode("",$oss_urls);
                        if(trim($bind['icon']) == "")
                        {
                            unset($bind['icon']);
                        }
                        //数据打包
                        $bind['detail'] = json_encode([]);
                        $bind['content'] = "";
                        //修改俱乐部
                        $res = $this->oClub->updateClub($bind['club_id'],$bind);
                        Base_Common::refreshCache($this->config,"club",$bind['club_id']);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }
        }
		echo json_encode($response);
		return true;
	}

	//删除俱乐部
	public function clubDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteClub");
		if($PermissionCheck['return'])
		{
			//俱乐部ID
			$club_id = trim($this->request->club_id);
			//删除俱乐部
			$this->oClub->deleteClub($club_id);
			//返回之前的俱乐部
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //成员列表
    public function memberListAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $club_id = intval($this->request->club_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            $params['status'] = 1;
            $params['club_id'] = $club_id;
            //获取俱乐部信息
            $clubInfo = $this->oClub->getClub($club_id,'*');
            //获取文章列表
            $memberList = $this->oClubMember->getMemberList($params);
            $userList = [];
            $defaultUserImg = (new Widget_Config())->getConfig("default_user_img");
            if(isset($defaultUserImg['config_sign']))
            {
                $defaultUserImg['content'] = json_decode($defaultUserImg['content'],true);
                $defaultUserImg = $defaultUserImg['content']['0']['img_url']??"";
            }
            else
            {
                $defaultUserImg = "";
            }
            //循环页面列表
            foreach($memberList['MemberList'] as $key => $memberDetail)
            {
                //数据解包
                if(!isset($userList[$memberDetail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($memberDetail['user_id'],'user_id,true_name,user_img');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$memberDetail['user_id']] = $userInfo;
                    }
                }
                $memberList['MemberList'][$key]['user_name'] = $userList[$memberDetail['user_id']]['true_name']??"未知用户";
                $memberList['MemberList'][$key]['user_img'] = $userList[$memberDetail['user_id']]['user_img']??$defaultUserImg;
                $memberList['MemberList'][$key]['detail'] = json_decode($memberDetail['detail'],true);

            }
            $page_url = Base_Common::getUrl('',$this->ctl,'member.list',$params)."&Page=~page~";
            $page_content =  base_common::multi($memberList['MemberCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_Club_MemberList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //成员列表
    public function memberLogAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $club_id = intval($this->request->club_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            $params['status'] = 1;
            $params['club_id'] = $club_id;
            //获取俱乐部信息
            $clubInfo = $this->oClub->getClub($club_id,'*');
            //俱乐部记录列表
            $logList = $this->oClubMember->getMemberLogList($params);
            $userList = [];
            //循环页面列表
            foreach($logList['LogList'] as $key => $logDetail)
            {
                //数据解包
                if(!isset($userList[$logDetail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($logDetail['user_id'],'user_id,true_name,user_img');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$logDetail['user_id']] = $userInfo;
                    }
                }
                //数据解包
                if(!isset($userList[$logDetail['operate_user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($logDetail['operate_user_id'],'user_id,true_name,user_img');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$logDetail['operate_user_id']] = $userInfo;
                    }
                }
                //数据解包
                if(!isset($userList[$logDetail['process_user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($logDetail['process_user_id'],'user_id,true_name,user_img');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$logDetail['process_user_id']] = $userInfo;
                    }
                }
                $logList['LogList'][$key]['user_name'] = $userList[$logDetail['user_id']]['true_name']??"未知用户";
                $logList['LogList'][$key]['operate_user_name'] = $userList[$logDetail['operate_user_id']]['true_name']??"未知用户";
                $logList['LogList'][$key]['process_user_name'] = $userList[$logDetail['process_user_id']]['true_name']??"未知用户";
                //$logList['LogList'][$key]['user_img'] = $userList[$logDetail['user_id']]['user_img']??"";
                $logList['LogList'][$key]['detail'] = json_decode($logDetail['detail'],true);
                $logList['LogList'][$key]['action_name'] = $this->oClubMember->processMemberAction($logDetail['type'],$logDetail['sub_type']);
                $logList['LogList'][$key]['result_name'] = $this->oClubMember->processMemberLogResult($logDetail['user_id'],$logDetail['operate_user_id'],$logDetail['process_user_id'],$logDetail['result']);
            }
            $page_url = Base_Common::getUrl('',$this->ctl,'member.log',$params)."&Page=~page~";
            $page_content =  base_common::multi($logList['LogCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_Club_MemberLogList');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加俱乐部banner页面
    public function bannerAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateClub");
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $club_id= intval($this->request->club_id);
            //获取俱乐部信息
            $clubInfo = $this->oClub->getClub($club_id,"club_id");
            //渲染模版
            include $this->tpl('Hj_Club_BannerAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加俱乐部banner
    public function bannerInsertAction()
    {
        //俱乐部ID
        $club_id = intval($this->request->club_id);
        $detail = $this->request->detail;
        $clubInfo = $this->oClub->getClub($club_id,"club_id,detail");
        $clubInfo['detail'] = json_decode($clubInfo['detail'],true);
        //上传图片
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        //如果没有成功上传
        if(!isset($oss_urls['0']) || $oss_urls['0'] == "")
        {
            $response = array('errno' => 1);
        }
        else
        {
            $clubInfo['detail']['banner'] = $clubInfo['detail']['banner']??[];
            $clubInfo['detail']['banner'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url'],'text'=>trim($detail['text']??""),'title'=>trim($detail['title']??"")];
            $clubInfo['detail'] = json_encode($clubInfo['detail']);
            $res = $this->oClub->updateclub($club_id,$clubInfo);
            Base_Common::refreshCache($this->config,"club",$club_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        echo json_encode($response);
        return true;
    }
    //修改俱乐部Banner页面
    public function bannerModifyAction()
    {
        //元素ID
        $club_id = intval($this->request->club_id);
        $pos = intval($this->request->pos??0);
        $clubInfo = $this->oClub->getClub($club_id,"club_id,detail");
        $clubInfo['detail'] = json_decode($clubInfo['detail'],true);
        $bannerInfo = $clubInfo['detail']['banner'][$pos];
        //渲染模版
        include $this->tpl('Hj_Club_BannerModify');
    }
    //更新页面元素详情
    public function bannerUpdateAction()
    {
        //俱乐部ID
        $club_id = intval($this->request->club_id);
        $detail = $this->request->detail;
        $clubInfo = $this->oClub->getClub($club_id,"club_id,detail");
        $clubInfo['detail'] = json_decode($clubInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        //上传图片
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        //如果以前没上传过且这次也没有成功上传
        if((!isset($clubInfo['detail']['banner'][$pos]['img_url']) || $clubInfo['detail']['banner'][$pos]['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
        {
            $response = array('errno' => 2);
        }
        else
        {
            //这次传成功了就用这次，否则维持
            $clubInfo['detail']['banner'][$pos]['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($clubInfo['detail']['banner'][$pos]['img_url']);
            //保存跳转链接
            $clubInfo['detail']['banner'][$pos]['img_jump_url'] = trim($detail['img_jump_url']??"");
            $clubInfo['detail']['banner'][$pos]['text'] = trim($detail['text']??"");
            $clubInfo['detail']['banner'][$pos]['title'] = trim($detail['title']??"");
            $clubInfo['detail'] = json_encode($clubInfo['detail']);
            $res = $this->oClub->updateClub($club_id,$clubInfo);
            Base_Common::refreshCache($this->config,"club",$club_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除页面banner详情
    public function bannerDeleteAction()
    {
        //俱乐部ID
        $club_id = intval($this->request->club_id);
        $detail = $this->request->detail;
        $clubInfo = $this->oClub->getClub($club_id,"club_id,detail");
        $clubInfo['detail'] = json_decode($clubInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        if(isset($clubInfo['detail']['banner'][$pos]))
        {
            unset($clubInfo['detail']['banner'][$pos]);
            $clubInfo['detail']['banner'] = array_values($clubInfo['detail']['banner']);
            $clubInfo['detail'] = json_encode($clubInfo['detail']);
            $res = $this->oClub->updateClub($club_id,$clubInfo);
            Base_Common::refreshCache($this->config,"club",$club_id);
        }
        $this->response->goBack();
    }
    //俱乐部邀请成员加入
    public function inviteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateClub");
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $club_id= intval($this->request->club_id);
            //获取俱乐部信息
            $clubInfo = $this->oClub->getClub($club_id,'*');
            $inviteUrl = $this->config->apiUrl.$this->config->api['api']['invite'];
            $token = (new Hj_UserInfo())->getTokenForManager($this->manager->id);
            //渲染模版
            include $this->tpl('Hj_Club_Invite');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //根据输入的用户信息搜索列表以供邀请
    public function getUserForInviteAction()
    {
        //俱乐部ID
        $club_id = intval($this->request->club_id);
        //姓名
        $user_name = trim($this->request->user_name);
        //获取俱乐部信息
        $clubInfo = $this->oClub->getClub($club_id,'*');
        $text = '';
        $text .= '<option value="0">请选择</option>';
        if($user_name!="")
        {
            //获取用户列表
            $UserList = $this->oUserInfo->getUserList(['company_id'=>$clubInfo['company_id'],"true_name"=>$user_name,"Page"=>1,"PageSize"=>20]);
            if(isset($UserList['UserList']))
            {
                foreach($UserList['UserList'] as $key => $userInfo)
                {
                    $exist = $this->oClubMember->getMemberCount(['club_id'=>$club_id,"status"=>1,"user_id"=>$userInfo['user_id']]);
                    if($exist>0)
                    {
                        $text .= '<option value="'.$userInfo['user_id'].'" disabled="disabled">'.$userInfo['true_name'].'(已加入)</option>';
                    }
                    else
                    {
                        $text .= '<option value="'.$userInfo['user_id'].'">'.$userInfo['true_name'].'</option>';
                    }

                }
            }
        }
        echo $text;
        die();
    }
}
