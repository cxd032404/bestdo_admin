<?php
/**
 * 投票管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_VoteController extends AbstractController
{
	/**活动:Acitvity
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
	//活动配置列表活动
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//企业ID
            $activity_id = intval($this->request->activity_id??0);
			//获取活动列表
			$voteList = $this->oVote->getVoteList(['activity_id'=>$activity_id]);
            //获取活动列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
            //循环活动列表
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
	//添加活动类型填写配置活动
	public function voteAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addVote");
		if($PermissionCheck['return'])
		{
            //获取活动列表
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

	//添加新活动
	public function voteInsertAction()
	{
		//检查权限
		$bind=$this->request->from('vote_name','activity_id','vote_sign','start_time','end_time');
		//活动名称不能为空
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
                        //添加活动
                        $res = $this->oVote->insertVote($bind);
                        $response = $res ? array('errno' => 0) : array('errno' => 9);
                    }
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//修改活动信息活动
	public function voteModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateVote");
		if($PermissionCheck['return'])
		{
			//活动ID
			$vote_id= intval($this->request->vote_id);
			//获取活动信息
			$voteInfo = $this->oVote->getVote($vote_id,'*');
            //获取活动列表
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

	//更新活动信息
	public function voteUpdateAction()
	{
	    //接收活动参数
        $bind=$this->request->from('vote_id','vote_name','activity_id','vote_sign','start_time','end_time');
        //活动名称不能为空
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
                $voteExists = $this->oVote->getVoteList(['company_id'=>$bind['company_id'],'vote_sign'=>$bind['vote_sign'],'exclude_id'=>$bind['vote_id']],'vote_id,vote_sign');
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
                    //修改活动
                    $res = $this->oVote->updateVote($bind['vote_id'],$bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
            }
		}
		echo json_encode($response);
		return true;
	}

	//删除活动
	public function voteDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteVote");
		if($PermissionCheck['return'])
		{
			//活动ID
			$vote_id = trim($this->request->vote_id);
			//删除活动
			$this->oVote->deleteVote($vote_id);
			//返回之前的活动
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}
