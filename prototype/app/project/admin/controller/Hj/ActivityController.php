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
    protected $oList;
    protected $oPosts;

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
        $this->oList = new Hj_List();
        $this->oPosts = new Hj_Posts();
    }
	//活动配置列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //企业ID
            $params['company_id'] = intval($this->request->company_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            $params['getCount'] = 1;
			//获取活动列表
			$activityList = $this->oActivity->getActivityList(array_merge($params,["permissionList"=>$totalPermission]));
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
            $userList = [];
            //循环活动列表
			foreach($activityList['ActivityList'] as $key => $activityInfo)
            {
                //数据解包
                $activityList['ActivityList'][$key]['detail'] = json_decode($activityInfo['detail'],true);
				$activityList['ActivityList'][$key]['company_name'] = ($activityInfo['company_id']==0)?"无对应":($companyList[$activityInfo['company_id']]['company_name']??"未知");
                $params['activity_id'] = $activityInfo['activity_id'];
                //获取报名记录数量
                $activityList['ActivityList'][$key]['count']  = $this->oUserInfo->getUserActivityLogCount($params);
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
                $list_info = $this->oList->getlistsWithActivityId($activityInfo['activity_id']);
                if($list_info)
                {
                    $activityList['ActivityList'][$key]['download'] = 1;
                }else
                {
                    $activityList['ActivityList'][$key]['download'] = 0;
                }

                $activityList['ActivityList'][$key]['create_user_name'] = $userList[$activityInfo['create_user_id']]['true_name']??"未知用户";
                $activityList['ActivityList'][$key]['club_name'] = $clubList[$activityInfo['club_id']]['club_name']??"未指定";
            }
            $page_url = Base_Common::getUrl('',$this->ctl,'index',$params)."&Page=~page~";
            $page_content =  base_common::multi($activityList['ActivityCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');




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
		$PermissionCheck = $this->manager->checkMenuPermission("addActivity",$this->sign);
		if($PermissionCheck['return'])
		{
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取顶级活动列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
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
                    Base_Common::refreshCache($this->config,"activity",$res);
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
		$PermissionCheck = $this->manager->checkMenuPermission("updateActivity",$this->sign);
		if($PermissionCheck['return'])
		{
			//活动ID
			$activity_id= intval($this->request->activity_id);
			//获取活动信息
			$activityInfo = $this->oActivity->getActivity($activity_id,'*');
			//数据解包
            $activityInfo['detail'] = json_decode($activityInfo['detail'],true);
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
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
            Base_Common::refreshCache($this->config,"activity",$bind['activity_id']);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	//删除活动
	public function activityDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteActivity",$this->sign);
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
		$PermissionCheck = $this->manager->checkMenuPermission("updatePage",$this->sign);
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
            $totalPermission = $this->manager->getPermissionList($this->manager->data_groups,"only");
            //获取企业列表
			$companyList = $this->oCompany->getCompanyList(["permissionList"=>$totalPermission],"company_id,company_name");
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
        foreach($activityList['ActivityList'] as $activityInfo)
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
        $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
        if($PermissionCheck['return'])
        {
            //列表ID
            $activity_id = intval($this->request->activity_id??0);
            //分页参数
            $params['Page'] = abs(intval($this->request->Page??1));
            $params['PageSize'] = 20;
            //获取列表时需要获得记录总数
            $params['getCount'] = 1;
            //获取活动信息
            $activityInfo = $this->oActivity->getActivity($activity_id,'*');
            //数据解包
            $activityInfo['detail'] = json_decode($activityInfo['detail'],true);
            $params['activity_id'] = $activityInfo['activity_id'];
            //获取活动列表
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
    
    /*
     * 下载活动详细信息
     */
    public function activityDownloadAction()
    {
        $activity_id = $this->request->get('activity_id')??0;
        $activityInfo = $this->oActivity->getActivity($activity_id, 'activity_id,activity_name');
        $activity_name = $activityInfo['activity_name'];
        $list_info = $this->oList->getlistsWithActivityId($activity_id);


        $oExcel = new Third_Excel();
        $FileName = (iconv('gbk', 'utf-8', $activity_name . '参赛作品'));
        $oExcel_file = $oExcel->download($FileName);
        //多个列表
        foreach ($list_info as $kye => $value) {
            $userList = [];
            $post_list = $this->oPosts->getPostWithList($value['list_id']);
            foreach ($post_list as $key =>$post_info)
            {
                $user_info = $this->oUserInfo->getUser($post_info['user_id'],'user_id,true_name');
                $userList[$key]['user_id'] = $user_info['user_id']??$post_info['user_id'];
                $userList[$key]['true_name'] = $user_info['true_name']??'未知用户';
                $userList[$key]['kudosSum'] = $post_info['kudosSum'];
                $userList[$key]['postCount'] = $post_info['postCount'];
            }

            $oExcel_file->addSheet($value['list_name']);
            //标题栏
            $title = array("用户ID", "姓名", "文章数量","投票数");
            $oExcel->addRows(array($title));
            //
            foreach ($userList as $userId => $userInfo) {
                $t = array();
                $t['UserId'] = $userInfo['user_id'];
                $t['UserName'] = $userInfo['true_name'];
                $t['postCount'] = $userInfo['postCount'];
                $t['kudosSum'] = $userInfo['kudosSum'];
                $oExcel->addRows(array($t));
                unset($t);
            }
            unset($userList);
            $oExcel->closeSheet();
        }
        $oExcel->close();
    }

    /*
     * 上传页面渲染
     */public function activityJudgeUploadSubmitAction()
{
    $activity_id = $this->request->get("activity_id")??0;
    //检查权限
    $PermissionCheck = $this->manager->checkMenuPermission(0,$this->sign);
    if($PermissionCheck['return'])
    {
        //模板渲染
        include $this->tpl('Hj_Activity_ActivityJudgeUpload');
    }
    else
    {
        $home = $this->sign;
        include $this->tpl('403');
    }
}


    /*
     * 评价导入
     */
    public function activityJudgeUploadAction(){
        $activity_id = $this->request->activity_id??0;
        $oUpload = new Base_Upload('upload_txt');
        $upload = $oUpload->upload('upload_txt');
        $upload = $upload->resultArr;
        print_r($upload);die();
        if($upload[1]['errno']==0) {
            //$index = (new Base_Cache_Elasticsearch())->checkIndex("company_user_list",['company_id'=>$company_id]);
            $file_path = $upload[1]['path'];
        }

        require_once "../../../../lib/Third/PHPExcel.php";
        $inputFileType = PHPExcel_IOFactory::identify($file_path);
        print_r($inputFileType);die();
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);

        $PHPExcel = $objReader->load($file_path); //读取文件
        print_r($PHPExcel);DIE();
        $currentSheet = $PHPExcel->getSheet("0"); //读取第一个工作簿
        $allColumn = $currentSheet->getHighestColumn(); // 所有列数
        $allRow = $currentSheet->getHighestRow(); // 所有行数
        $data = array(); //下面是读取想要获取的列的内容
        for ($rowIndex = 2; $rowIndex <= $allRow; $rowIndex++)
        {
            $data[] = array(
                'id' => $cell = $currentSheet->getCell('A'.$rowIndex)->getValue(),
                'score' => $cell = $currentSheet->getCell('H'.$rowIndex)->getValue(),
                'ranking' => $cell = $currentSheet->getCell('I'.$rowIndex)->getValue(),
             );
         }
        print_r($data);die();
    }
}
