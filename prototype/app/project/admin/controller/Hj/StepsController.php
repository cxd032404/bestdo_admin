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
            $userList  = $goalList = [];
			foreach($StepsDetailList['DetailList'] as $key => $detail)
            {
                if(!isset($userList[$detail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($detail['user_id'],'user_id,true_name');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$detail['user_id']] = $userInfo;
                    }
                }
                $StepsDetailList['DetailList'][$key]['user_name'] = $userList[$detail['user_id']]['true_name']??"未知用户";
                $StepsDetailList['DetailList'][$key]['kcal'] = intval($detail['step']/$this->config->stepsPerKcal);
                $StepsDetailList['DetailList'][$key]['distance'] = intval($this->config->distancePerStep*$detail['step']);
                $StepsDetailList['DetailList'][$key]['company_name'] = $companyList[$detail['company_id']]['company_name']??"未知企业";
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

			//渲染模版
			include $this->tpl('Hj_Steps_UserDetailList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加俱乐部类型填写配置俱乐部
	public function StepsAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addSteps");
		if($PermissionCheck['return'])
		{
		    //获取顶级俱乐部列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
			//渲染模版
			include $this->tpl('Hj_Steps_StepsAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新俱乐部
	public function StepsInsertAction()
	{
		$connectedUser = (new Hj_UserInfo())->getConnectedUserInfo($this->manager->id);
		if(!$connectedUser['user_id'])
        {
            $response = array('errno' => 8);
        }
		else
        {
            //检查权限
            $bind=$this->request->from('Steps_name','company_id','Steps_sign','member_limit','allow_enter');
            //俱乐部名称不能为空
            if(trim($bind['Steps_name'])=="")
            {
                $response = array('errno' => 1);
            }
            else
            {
                if(trim($bind['Steps_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $StepsExists = $this->oSteps->getStepsList(['company_id'=>$bind['company_id'],'Steps_sign'=>$bind['Steps_sign']],'Steps_id,Steps_sign');
                    if(count($StepsExists)>0)
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
                            $res = $this->oSteps->insertSteps($bind);
                            if($res)
                            {
                                Base_Common::refreshCache($this->config,"Steps",$res);
                                $memberInfo = ['Steps_id'=>$res,
                                    'company_id'=>$bind['company_id'],
                                    'user_id'=>$connectedUser['user_id'],
                                    'permission'=>9,'status'=>1,
                                    'detail'=>json_encode(['comment'=>"俱乐部创建自动加入"]),];
                                $memberLogInfo = ['Steps_id'=>$res,
                                    'company_id'=>$bind['company_id'],
                                    'type'=>1,'sub_type'=>1,
                                    'user_id'=>$connectedUser['user_id'],
                                    'operate_user_id'=>$connectedUser['user_id'],
                                    'process_user_id'=>$connectedUser['user_id'],
                                    'detail'=>json_encode(['comment'=>"俱乐部创建自动加入"]),
                                    'result'=>1];
                                //写入成员
                                $member = $this->oStepsMember->insertStepsMember($memberInfo);
                                //写入成员日志
                                $log = $this->oStepsMember->insertStepsMemberLog($memberLogInfo);
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
	public function StepsModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateSteps");
		if($PermissionCheck['return'])
		{
			//俱乐部ID
			$Steps_id= intval($this->request->Steps_id);
			//获取俱乐部信息
			$StepsInfo = $this->oSteps->getSteps($Steps_id,'*');
			//数据解包
            $StepsInfo['detail'] = json_decode($StepsInfo['detail'],true);
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
            //渲染模版
			include $this->tpl('Hj_Steps_StepsModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //修改俱乐部信息
    public function bannerAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateSteps");
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $Steps_id= intval($this->request->Steps_id);
            //获取俱乐部信息
            $StepsInfo = $this->oSteps->getSteps($Steps_id,'*');
            //数据解包
            $StepsInfo['detail'] = json_decode($StepsInfo['detail'],true);
            //渲染模版
            include $this->tpl('Hj_Steps_Banner');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }

	//更新俱乐部信息
	public function StepsUpdateAction()
	{
        $connectedUser = (new Hj_UserInfo())->getConnectedUserInfo($this->manager->id);
        if(!$connectedUser['user_id'])
        {
            $response = array('errno' => 8);
        }
        else
        {
            //接收俱乐部参数
            $bind=$this->request->from('Steps_id','Steps_name','company_id','Steps_sign','member_limit','allow_enter');
            //俱乐部名称不能为空
            if(trim($bind['Steps_name'])=="")
            {
                $response = array('errno' => 1);
            }
            else
            {
                if(trim($bind['Steps_sign'])=="")
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $StepsExists = $this->oSteps->getStepsList(['company_id'=>$bind['company_id'],'Steps_sign'=>$bind['Steps_sign'],'exclude_id'=>$bind['Steps_id']],'Steps_id,Steps_sign');
                    if(count($StepsExists)>0)
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
                        $res = $this->oSteps->updateSteps($bind['Steps_id'],$bind);
                        Base_Common::refreshCache($this->config,"Steps",$bind['Steps_id']);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }
        }
		echo json_encode($response);
		return true;
	}

	//删除俱乐部
	public function StepsDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteSteps");
		if($PermissionCheck['return'])
		{
			//俱乐部ID
			$Steps_id = trim($this->request->Steps_id);
			//删除俱乐部
			$this->oSteps->deleteSteps($Steps_id);
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
            $Steps_id = intval($this->request->Steps_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            $params['status'] = 1;
            $params['Steps_id'] = $Steps_id;
            //获取俱乐部信息
            $StepsInfo = $this->oSteps->getSteps($Steps_id,'*');
            //获取文章列表
            $memberList = $this->oStepsMember->getMemberList($params);
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
            include $this->tpl('Hj_Steps_MemberList');
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
            $Steps_id = intval($this->request->Steps_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 2;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            $params['status'] = 1;
            $params['Steps_id'] = $Steps_id;
            //获取俱乐部信息
            $StepsInfo = $this->oSteps->getSteps($Steps_id,'*');
            //俱乐部记录列表
            $logList = $this->oStepsMember->getMemberLogList($params);
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
                $logList['LogList'][$key]['action_name'] = $this->oStepsMember->processMemberAction($logDetail['type'],$logDetail['sub_type']);
                $logList['LogList'][$key]['result_name'] = $this->oStepsMember->processMemberLogResult($logDetail['user_id'],$logDetail['operate_user_id'],$logDetail['process_user_id'],$logDetail['result']);
            }
            $page_url = Base_Common::getUrl('',$this->ctl,'member.log',$params)."&Page=~page~";
            $page_content =  base_common::multi($logList['LogCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_Steps_MemberLogList');
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
        $PermissionCheck = $this->manager->checkMenuPermission("updateSteps");
        if($PermissionCheck['return'])
        {
            //俱乐部ID
            $Steps_id= intval($this->request->Steps_id);
            //获取俱乐部信息
            $StepsInfo = $this->oSteps->getSteps($Steps_id,"Steps_id");
            //渲染模版
            include $this->tpl('Hj_Steps_BannerAdd');
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
        $Steps_id = intval($this->request->Steps_id);
        $detail = $this->request->detail;
        $StepsInfo = $this->oSteps->getSteps($Steps_id,"Steps_id,detail");
        $StepsInfo['detail'] = json_decode($StepsInfo['detail'],true);
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
            $StepsInfo['detail']['banner'] = $StepsInfo['detail']['banner']??[];
            $StepsInfo['detail']['banner'][] = ['img_url'=>$oss_urls['0'],'img_jump_url'=>$detail['img_jump_url'],'text'=>trim($detail['text']??""),'title'=>trim($detail['title']??"")];
            $StepsInfo['detail'] = json_encode($StepsInfo['detail']);
            $res = $this->oSteps->updateSteps($Steps_id,$StepsInfo);
            Base_Common::refreshCache($this->config,"Steps",$Steps_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
        echo json_encode($response);
        return true;
    }
    //修改俱乐部Banner页面
    public function bannerModifyAction()
    {
        //元素ID
        $Steps_id = intval($this->request->Steps_id);
        $pos = intval($this->request->pos??0);
        $StepsInfo = $this->oSteps->getSteps($Steps_id,"Steps_id,detail");
        $StepsInfo['detail'] = json_decode($StepsInfo['detail'],true);
        $bannerInfo = $StepsInfo['detail']['banner'][$pos];
        //渲染模版
        include $this->tpl('Hj_Steps_BannerModify');
    }
    //更新页面元素详情
    public function bannerUpdateAction()
    {
        //俱乐部ID
        $Steps_id = intval($this->request->Steps_id);
        $detail = $this->request->detail;
        $StepsInfo = $this->oSteps->getSteps($Steps_id,"Steps_id,detail");
        $StepsInfo['detail'] = json_decode($StepsInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        //上传图片
        $oUpload = new Base_Upload('upload_img');
        $upload = $oUpload->upload('upload_img',$this->config->oss);
        $oss_urls = array_column($upload->resultArr,'oss');
        //如果以前没上传过且这次也没有成功上传
        if((!isset($StepsInfo['detail']['banner'][$pos]['img_url']) || $StepsInfo['detail']['banner'][$pos]['img_url']=="") && (!isset($oss_urls['0']) || $oss_urls['0'] == ""))
        {
            $response = array('errno' => 2);
        }
        else
        {
            //这次传成功了就用这次，否则维持
            $StepsInfo['detail']['banner'][$pos]['img_url'] = (isset($oss_urls['0']) && $oss_urls['0']!="")?($oss_urls['0']):($StepsInfo['detail']['banner'][$pos]['img_url']);
            //保存跳转链接
            $StepsInfo['detail']['banner'][$pos]['img_jump_url'] = trim($detail['img_jump_url']??"");
            $StepsInfo['detail']['banner'][$pos]['text'] = trim($detail['text']??"");
            $StepsInfo['detail']['banner'][$pos]['title'] = trim($detail['title']??"");
            $StepsInfo['detail'] = json_encode($StepsInfo['detail']);
            $res = $this->oSteps->updateSteps($Steps_id,$StepsInfo);
            Base_Common::refreshCache($this->config,"Steps",$Steps_id);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
        }
        echo json_encode($response);
        return true;
    }
    //删除页面banner详情
    public function bannerDeleteAction()
    {
        //俱乐部ID
        $Steps_id = intval($this->request->Steps_id);
        $detail = $this->request->detail;
        $StepsInfo = $this->oSteps->getSteps($Steps_id,"Steps_id,detail");
        $StepsInfo['detail'] = json_decode($StepsInfo['detail'],true);
        $pos = intval($this->request->pos??0);
        if(isset($StepsInfo['detail']['banner'][$pos]))
        {
            unset($StepsInfo['detail']['banner'][$pos]);
            $StepsInfo['detail']['banner'] = array_values($StepsInfo['detail']['banner']);
            $StepsInfo['detail'] = json_encode($StepsInfo['detail']);
            $res = $this->oSteps->updateSteps($Steps_id,$StepsInfo);
            Base_Common::refreshCache($this->config,"Steps",$Steps_id);
        }
        $this->response->goBack();
    }
}
