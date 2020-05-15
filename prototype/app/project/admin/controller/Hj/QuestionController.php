<?php
/**
 * 提问管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Hj_QuestionController extends AbstractController
{
	/**提问:Question
	 * @var string
	 */
	protected $sign = '?ctl=hj/question';
    protected $ctl = 'hj/question';

    /**
	 * game对象
	 * @var object
	 */
	protected $oQuestion;
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
		$this->oQuestion = new Hj_Question();
        $this->oActivity = new Hj_Activity();

    }
	//提问配置列表提问
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//活动ID
            $activity_id = intval($this->request->activity_id??0);
			//获取提问列表
			$questionList = $this->oQuestion->getQuestionList(['activity_id'=>$activity_id]);
            //获取提问列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
            //循环提问列表
			foreach($questionList as $key => $questionInfo)
            {
                //数据解包
                $questionList[$key]['detail'] = json_decode($questionInfo['detail'],true);
				$questionList[$key]['activity_name'] = ($questionInfo['activity_id']==0)?"无对应":($activityList[$questionInfo['activity_id']]['activity_name']??"未知");
            }
			//渲染模版
			include $this->tpl('Hj_Question_QuestionList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加提问类型填写配置页面
	public function questionAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("addQuestion");
		if($PermissionCheck['return'])
		{
            //获取提问列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
			//渲染模版
			include $this->tpl('Hj_Question_QuestionAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//添加新提问
	public function questionInsertAction()
	{
		//检查权限
		$bind=$this->request->from('question','answer','activity_id','detail');
		//提问和回答不能为空
		if(trim($bind['question'])=="")
		{
			$response = array('errno' => 1);
		}
		else
		{
            if(trim($bind['answer'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $bind['detail']['keywords'] = explode("|",$bind['detail']['keywords']);
                //数据打包
                $bind['detail'] = json_encode($bind['detail']);
                //添加提问
                $res = $this->oQuestion->insertQuestion($bind);
                $bind['question_id'] = $res;
                $bind['detail'] = json_decode($bind['detail'],true);
                $index  = (new Base_Cache_Elasticsearch())->questionIndex($bind,$this->config->elasticsearch);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
		}
		echo json_encode($response);
		return true;
	}

	//修改提问信息页面
	public function questionModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("updateQuestion");
		if($PermissionCheck['return'])
		{
			//提问ID
			$question_id= intval($this->request->question_id);
			//获取提问信息
			$questionInfo = $this->oQuestion->getQuestion($question_id,'*');
            //数据解包
            $questionInfo['detail'] = json_decode($questionInfo['detail'],true);
            $questionInfo['detail']['keywords'] = implode("|",$questionInfo['detail']['keywords']);
            //获取提问列表
            $activityList = $this->oActivity->getActivityList([],"activity_id,activity_name");
            //渲染模版
			include $this->tpl('Hj_Question_QuestionModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	//更新提问信息
	public function questionUpdateAction()
	{
	    //接收提问参数
        $bind=$this->request->from('question_id','question','answer','activity_id','detail');
        if(trim($bind['question'])=="")
        {
            $response = array('errno' => 1);
        }
        else
        {
            if(trim($bind['answer'])=="")
            {
                $response = array('errno' => 3);
            }
            else
            {
                $bind['detail']['keywords'] = explode("|",$bind['detail']['keywords']);
                //数据打包
                $bind['detail'] = json_encode($bind['detail']);
                //修改提问
                $res = $this->oQuestion->updateQuestion($bind['question_id'],$bind);
                $bind['detail'] = json_decode($bind['detail'],true);
                $index  = (new Base_Cache_Elasticsearch())->questionIndex($bind,$this->config->elasticsearch);
                $response = $res ? array('errno' => 0) : array('errno' => 9);
            }
		}
		echo json_encode($response);
		return true;
	}

	//删除提问
	public function questionDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("deleteQuestion");
		if($PermissionCheck['return'])
		{
			//提问ID
			$question_id = trim($this->request->question_id);
			//删除提问
			$this->oQuestion->deleteQuestion($question_id);
			//返回之前的提问
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}
