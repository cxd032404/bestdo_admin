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

	}
	//活动配置列表活动
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
			//循环活动列表
			foreach($activityList as $key => $activityInfo)
            {
                //数据解包
                $activityList[$key]['detail'] = json_decode($activityInfo['detail'],true);
				$activityList[$key]['company_name'] = ($activityInfo['company_id']==0)?"无对应":($companyList[$activityInfo['company_id']]['company_name']??"未知");
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
		//检查权限
		$bind=$this->request->from('activity_name','company_id','activity_sign','start_time','end_time','apply_start_time','apply_end_time');
		//活动名称不能为空
		if(trim($bind['activity_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['activity_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $activityExists = $this->oActivity->getActivityList(['company_id'=>$bind['company_id'],'activity_sign'=>$bind['activity_sign']],'activity_id,activity_sign');
                if(count($activityExists)>0)
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
                        $bind['detail'] = [];
                        //数据打包
                        $bind['detail'] = json_encode($bind['detail']);
                        //添加活动
                        $res = $this->oActivity->insertActivity($bind);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
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
			//获取企业列表
			$companyList = $this->oCompany->getCompanyList([],"company_id,company_name");
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
        $bind=$this->request->from('activity_id','activity_name','company_id','activity_sign','start_time','end_time','apply_start_time','apply_end_time');
        //活动名称不能为空
		if(trim($bind['activity_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['activity_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $activityExists = $this->oActivity->getActivityList(['company_id'=>$bind['company_id'],'activity_sign'=>$bind['activity_sign'],'exclude_id'=>$bind['activity_id']],'activity_id,activity_sign');
                if(count($activityExists)>0)
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
                    //修改活动
                    $res = $this->oActivity->updateActivity($bind['activity_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
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
}
