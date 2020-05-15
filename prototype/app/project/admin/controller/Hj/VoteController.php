<?php
/**
 * 投票管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_VoteController extends AbstractController
{
	/**投票:Vote
	 * @var string
	 */
	protected $sign = '?ctl=hj/vote';
    protected $ctl = 'hj/vote';

    /**
	 * game对象
	 * @var object
	 */
	protected $oVote;
	protected $oCompany;
    protected $oActivity;


    /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oVote = new Hj_Vote();
        $this->oActivity = new Hj_Activity();

    }
	//投票配置列表投票
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//活动ID
            $activity_id = intval($this->request->activity_id??0);
			//获取投票列表
			$voteList = $this->oVote->getVoteList(['activity_id'=>$activity_id]);
            //获取投票列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
            //循环投票列表
			foreach($voteList as $key => $voteInfo)
            {
                //数据解包
                $voteList[$key]['detail'] = json_decode($voteInfo['detail'],true);
				$voteList[$key]['activity_name'] = ($voteInfo['activity_id']==0)?"无对应":($activityList[$voteInfo['activity_id']]['activity_name']??"未知");
            }
			//渲染模版
			include $this->tpl('Hj_Vote_VoteList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加投票类型填写配置页面
	public function voteAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addVote");
		if($PermissionCheck['return'])
		{
            //获取投票列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
			//渲染模版
			include $this->tpl('Hj_Vote_VoteAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新投票
	public function voteInsertAction()
	{
		//检查权限
		$bind=$this->request->from('vote_name','activity_id','vote_sign','start_time','end_time');
		//投票名称不能为空
		if(trim($bind['vote_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['vote_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $voteExists = $this->oVote->getVoteList(['activity_id'=>$bind['activity_id'],'vote_sign'=>$bind['vote_sign']],'vote_id,vote_sign');
                if(count($voteExists)>0)
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
                        //添加投票
                        $res = $this->oVote->insertVote($bind);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//修改投票信息页面
	public function voteModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateVote");
		if($PermissionCheck['return'])
		{
			//投票ID
			$vote_id= intval($this->request->vote_id);
			//获取投票信息
			$voteInfo = $this->oVote->getVote($vote_id,'*');
            //获取投票列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
            //渲染模版
			include $this->tpl('Hj_Vote_VoteModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新投票信息
	public function voteUpdateAction()
	{
	    //接收投票参数
        $bind=$this->request->from('vote_id','vote_name','activity_id','vote_sign','start_time','end_time');
        //投票名称不能为空
		if(trim($bind['vote_name'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['vote_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $voteExists = $this->oVote->getVoteList(['activity_id'=>$bind['activity_id'],'vote_sign'=>$bind['vote_sign'],'exclude_id'=>$bind['vote_id']],'vote_id,vote_sign');
                if(count($voteExists)>0)
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
                    //修改投票
                    $res = $this->oVote->updateVote($bind['vote_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//删除投票
	public function voteDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteVote");
		if($PermissionCheck['return'])
		{
			//投票ID
			$vote_id = trim($this->request->vote_id);
			//删除投票
			$this->oVote->deleteVote($vote_id);
			//返回之前的投票
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //添加投票类型填写配置页面
    public function voteDetailAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission(0);
        if($PermissionCheck['return'])
        {
            //投票ID
            $vote_id= intval($this->request->vote_id);
            //获取投票信息
            $voteInfo = $this->oVote->getVote($vote_id,'*');
            $voteInfo['detail'] = json_decode($voteInfo['detail'],true);
            //获取列表类型列表
            $listTypeList = (new Hj_List())->getListType();
            foreach($voteInfo['detail'] as $optionSign => $optionDetail)
            {
                if($optionDetail['source_from']=="from_list")
                {
                    $listInfo = (new Hj_List())->getList($optionDetail['list_id'],'list_id,list_name,list_type');
                    $listInfo['type_name'] = $listTypeList[$listInfo['list_type']]['name'];
                    $voteInfo['detail'][$optionSign]['source_from_name'] = "列表：".$listInfo['list_name']."|".$listInfo['type_name'];
                }
                else
                {
                    $voteInfo['detail'][$optionSign]['source_from_name'] = "无";
                }
            }
            //渲染模版
            include $this->tpl('Hj_Vote_VoteDetail');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加投票类型填写配置页面
    public function voteOptionAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateVote");
        if($PermissionCheck['return'])
        {
            //投票ID
            $vote_id= intval($this->request->vote_id);
            //获取投票信息
            $voteInfo = $this->oVote->getVote($vote_id,'*');
            $voteInfo['detail'] = json_decode($voteInfo['detail'],true);
            //获取活动信息
            $activityInfo = (new Hj_Activity())->getActivity($voteInfo['activity_id'],'activity_id,company_id');
            //获取列表列表
            $listList = (new Hj_List())->getListList(['company_id'=>$activityInfo['company_id']],"list_id,list_name");
            //渲染模版
            include $this->tpl('Hj_Vote_VoteOptionAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加投票类型填写配置页面
    public function voteOptionModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateVote");
        if($PermissionCheck['return'])
        {
            //投票ID
            $vote_id = trim($this->request->vote_id);
            //投票选项标示
            $vote_option_sign = trim($this->request->vote_option_sign);
            //获取投票信息
            $voteInfo = $this->oVote->getVote($vote_id,'vote_id,detail');
            $voteInfo['detail'] = json_decode($voteInfo['detail'],true);
            $optionDetail = $voteInfo['detail'][$vote_option_sign];

            //获取活动信息
            $activityInfo = (new Hj_Activity())->getActivity($voteInfo['activity_id'],'activity_id,company_id');
            //获取列表列表
            $listList = (new Hj_List())->getListList(['company_id'=>$activityInfo['company_id']],"list_id,list_name");
            //渲染模版
            include $this->tpl('Hj_Vote_VoteOptionModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //更新投票信息
    public function voteOptionUpdateAction()
    {
        //接收投票参数
        $bind=$this->request->from('vote_id','vote_option_name','vote_option_sign','detail');
        //投票名称不能为空
        if(trim($bind['vote_option_name'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            if(trim($bind['vote_option_sign'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                //获取投票信息
                $voteInfo = $this->oVote->getVote($bind['vote_id'],'vote_id,detail');
                $voteInfo['detail'] = json_decode($voteInfo['detail'],true);
                if($bind['detail']['source_from'] == "from_list")
                {
                    $optionInfo  = ['vote_option_name'=>$bind['vote_option_name'],'source_from'=>"from_list","list_id"=>$bind['detail']['list_id']];
                }
                else
                {
                    $optionInfo  = ['vote_option_name'=>$bind['vote_option_name'],'source_from'=>"none"];
                }
                $voteInfo['detail'][trim($bind['vote_option_sign'])] = $optionInfo;
                $voteInfo['detail'] = json_encode($voteInfo['detail']);
                //修改投票
                $res = $this->oVote->updateVote($bind['vote_id'],$voteInfo);
                $response = $res ? array('errno' => 0) : array('errno' => 9);

            }
        }
        echo json_encode($response);
        return true;
    }
    //删除投票
    public function voteOptionDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("updateVote");
        if($PermissionCheck['return'])
        {
            //投票ID
            $vote_id = trim($this->request->vote_id);
            //投票选项标示
            $vote_option_sign = trim($this->request->vote_option_sign);
            //获取投票信息
            $voteInfo = $this->oVote->getVote($vote_id,'vote_id,detail');
            $voteInfo['detail'] = json_decode($voteInfo['detail'],true);
            unset($voteInfo['detail'][$vote_option_sign]);
            $voteInfo['detail'] = json_encode($voteInfo['detail']);
            //修改投票
            $res = $this->oVote->updateVote($vote_id,$voteInfo);
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
