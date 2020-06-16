<?php
/**
 * 活动管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_ActivityController extends AbstractController
{
	/**活动:Acitvity
	 * @var string
	 */
	protected $sign = '?ctl=hj/activity';
    protected $ctl = 'hj/activity';

    /**
	 * game对象
	 * @var object
	 */
	protected $oActivity;
	protected $oCompany;
	protected $oActivityElement;
    protected $oUserInfo;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oActivity = new Hj_Activity();
		$this->oCompany = new Hj_Company();
        $this->oUserInfo = new Hj_UserInfo();
        $this->oClub = new Hj_Club();
    }
	//活动配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//企业ID
			$company_id = intval($this->request->company_id??0);
			//获取活动列表
			$activityList = $this->oActivity->getActivityList(['company_id'=>$company_id]);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            $userList = [];
            //循环活动列表
			foreach($activityList as $key => $activityInfo)
            {
                //数据解包
                $activityList[$key]['detail'] = json_decode($activityInfo['detail'],true);
				$activityList[$key]['company_name'] = ($activityInfo['company_id']==0)?"无对应":($companyList[$activityInfo['company_id']]['company_name']??"未知");
                //分页参数
                $params['Page'] = 1;
                $params['PageSize'] = 1;
                //获取列表时需要获得记录总数
                $params['getCount'] = 1;
                $params['activity_id'] = $activityInfo['activity_id'];
                //获取报名记录数量
                $List = $this->oUserInfo->getUserActivityLog($params);
                $activityList[$key]['count'] = $List['UserCount']??0;
                $userList = $clubList = [];
                if(!isset($userList[$activityInfo['create_user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($activityInfo['create_user_id'],'user_id,true_name');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$activityInfo['create_user_id']] = $userInfo;
                    }
                }
                if($activityInfo['club_id']>0)
                {
                    if(!isset($clubList[$activityInfo['club_id']]))
                    {
                        $clubInfo = $this->oClub->getClub($activityInfo['club_id'],'club_id,club_name');
                        if(isset($clubInfo['club_id']))
                        {
                            $clubList[$activityInfo['club_id']] = $clubInfo;
                        }
                    }
                }
                $activityList[$key]['create_user_name'] = $userList[$activityInfo['create_user_id']]['true_name']??"未知用户";
                $activityList[$key]['club_name'] = $clubList[$activityInfo['club_id']]['club_name']??"未指定";
            }

			//渲染模版
			include $this->tpl('Hj_Activity_ActivityList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加活动类型填写配置活动
	public function activityAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addActivity");
		if($PermissionCheck['return'])
		{
			//获取顶级活动列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
			//渲染模版
			include $this->tpl('Hj_Activity_ActivityAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新活动
	public function activityInsertAction()
	{
        $connectedUser = (new Hj_UserInfo())->getConnectedUserInfo($this->manager->id);
        if(!$connectedUser['user_id'])
        {
            $response = array('errno' => 8);
        }
        else
        {
            //检查权限
            $bind=$this->request->from('activity_name','company_id','club_id','member_limit','start_time','end_time','apply_start_time','apply_end_time','detail');
            //活动名称不能为空
            if(trim($bind['activity_name'])=="")
            {
                $response = array('errno' => 1);
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
                    $bind['create_user_id'] = $connectedUser['user_id'];
                    //数据打包
                    $bind['detail'] = json_encode($bind['detail']);
                    //添加活动
                    $res = $this->oActivity->insertActivity($bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
        }

		echo json_encode($response);
		return true;
	}

	//修改活动信息活动
	public function activityModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateActivity");
		if($PermissionCheck['return'])
		{
			//活动ID
			$activity_id= intval($this->request->activity_id);
			//获取活动信息
			$activityInfo = $this->oActivity->getActivity($activity_id,'*');
			//数据解包
            $activityInfo['detail'] = json_decode($activityInfo['detail'],true);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //获取企业对应的俱乐部列表
            $clubList = $activityInfo['club_id']>0?$this->oClub->getClubList(["company_id"=>$activityInfo['company_id']],"club_id,club_name"):[];
            //渲染模版
			include $this->tpl('Hj_Activity_ActivityModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新活动信息
	public function activityUpdateAction()
	{
	    //接收活动参数
        $bind=$this->request->from('activity_id','activity_name','company_id','club_id','member_limit','start_time','end_time','apply_start_time','apply_end_time','detail');
        //活动名称不能为空
		if(trim($bind['activity_name'])=="")
		{
			$response = array('errno' => 1);
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
            //获取原本的信息
            $activityInfo = $this->oActivity->getActivity($bind['activity_id'],"activity_id,detail");
            $activityInfo['detail'] = json_decode($activityInfo['detail'],true);
            //合并数据
            $bind['detail'] = array_merge($activityInfo['detail'],$bind['detail']);
            //数据打包
            $bind['detail'] = json_encode($bind['detail']);
            //修改活动
            $res = $this->oActivity->updateActivity($bind['activity_id'],$bind);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	//删除活动
	public function activityDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteActivity");
		if($PermissionCheck['return'])
		{
			//活动ID
			$activity_id = trim($this->request->activity_id);
			//删除活动
			$this->oActivity->deleteActivity($activity_id);
			//返回之前的活动
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//修改活动详情（元素列表）活动
	public function activityDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage");
		if($PermissionCheck['return'])
		{
			//活动ID
			$activity_id= intval($this->request->activity_id);
			//获取活动信息
			$activityInfo = $this->oActivity->getPage($activity_id,'*');
			//获取元素信息
			$activityElementList = $this->oActivityElement->getElementList(['activity_id'=>$activity_id]);
			//获取元素类型列表
			$elementTypeList = $this->oElementType->getElementTypeList();
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            foreach ($activityElementList as $elementSign => $elementInfo)
            {
            	$activityElementList[$elementSign]['element_type_name'] = $elementTypeList[$elementInfo['element_type']]['element_type_name']??"未知类型";
            }
            //渲染模版
			include $this->tpl('Hj_Activity_ActivityDetail');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //获取企业获取活动列表
    public function getActivityByCompanyAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
        //获取活动列表
        $activityList = $this->oActivity->getActivityList(['company_id'=>$company_id],"activity_id,activity_name");
        $text = '';
        $text .= '<option value="0">不指定</option>';
        //循环赛事分站列表
        foreach($activityList as $activityInfo)
        {
            //初始化选中状态
            $selected = "";
            /*
            //如果分站ID与传入的分站ID相符
            if($RaceStageInfo['RaceStageId'] == $StageId)
            {
                //选中拼接
                $selected = 'selected="selected"';
            }
            */
            //字符串拼接
            $text .= '<option value="'.$activityInfo['activity_id'].'">'.$activityInfo['activity_name'].'</option>';
        }
        echo $text;
        die();
    }
    //获取企业获取俱乐部列表
    public function getClubByCompanyAction()
    {
        //企业ID
        $company_id = intval($this->request->company_id);
        //获取活动列表
        //获取企业对应的俱乐部列表
        $clubList = $this->oClub->getClubList(["company_id"=>$company_id],"club_id,club_name");
        $text = '';
        $text .= '<option value="0">不指定</option>';
        //循环赛事分站列表
        foreach($clubList as $clubInfo)
        {
            //初始化选中状态
            $selected = "";
            /*
            //如果分站ID与传入的分站ID相符
            if($RaceStageInfo['RaceStageId'] == $StageId)
            {
                //选中拼接
                $selected = 'selected="selected"';
            }
            */
            //字符串拼接
            $text .= '<option value="'.$clubInfo['club_id'].'">'.$clubInfo['club_name'].'</option>';
        }
        echo $text;
        die();
    }
    //活动报名记录页面
    public function activityLogAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //列表ID
            $activity_id = intval($this->request->activity_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            //获取列表信息
            //获取活动信息
            $activityInfo = $this->oActivity->getActivity($activity_id,'*');
            //数据解包
            $activityInfo['detail'] = json_decode($activityInfo['detail'],true);
            $params['activity_id'] = $activityInfo['activity_id'];
            //获取文章列表
            $activityList = $this->oUserInfo->getUserActivityLog($params);
            $userList = [];
            //循环页面列表
            foreach($activityList['UserList'] as $key => $listDetail)
            {
                //数据解包
                if(!isset($userList[$listDetail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($listDetail['user_id'],'user_id,true_name');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$listDetail['user_id']] = $userInfo;
                    }
                }
                $activityList['UserList'][$key]['user_name'] = $userList[$listDetail['user_id']]['true_name']??"未知用户";
            }
            $page_url = Base_Common::getUrl('',$this->ctl,'activity.log',$params)."&Page=~page~";
            $page_content =  base_common::multi($activityList['UserCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_Activity_ActivityLog');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
