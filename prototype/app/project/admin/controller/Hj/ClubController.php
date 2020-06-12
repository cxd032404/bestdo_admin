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
			//企业ID
			$company_id = intval($this->request->company_id??0);
			//获取俱乐部列表
			$clubList = $this->oClub->getClubList(['company_id'=>$company_id]);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
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
		    //获取顶级俱乐部列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
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

	//修改俱乐部信息俱乐部
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
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
			include $this->tpl('Hj_Club_ClubModify');
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
            //获取俱乐部信息
            $clubInfo = $this->oClub->getClub($club_id,'*');
            //获取文章列表
            $memberList = $this->oClubMember->getMemberList($params);
            $userList = [];
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
                $memberList['MemberList'][$key]['user_img'] = $userList[$memberDetail['user_id']]['user_img']??"";
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
}
