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
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
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
//步数详情页面
    public function statAction()
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
            //开始日期
            $params['start_date']= trim($this->requrest->start_date??date("Y-m-d",time()-3*86400));
            //结束日期
            $params['end_date']= trim($this->requrest->end_date??date("Y-m-d"));
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
            $StepsStatList = $this->oSteps->getStepsStatList($params);
            $userList  = $goalList = [];
            $days = intval((strtotime($params['end_date']) - strtotime($params['start_date']))/86400);
            foreach($StepsStatList['UserList'] as $key => $detail)
            {
                if(!isset($userList[$detail['user_id']]))
                {
                    $userInfo = $this->oUserInfo->getUser($detail['user_id'],'user_id,true_name,company_id');
                    if(isset($userInfo['user_id']))
                    {
                        $userList[$detail['user_id']] = $userInfo;
                    }
                }
                $StepsStatList['UserList'][$key]['user_name'] = $userList[$detail['user_id']]['true_name']??"未知用户";
                $StepsStatList['UserList'][$key]['kcal'] = intval($detail['totalStep']/$this->config->stepsPerKcal);
                $StepsStatList['UserList'][$key]['distance'] = intval($this->config->distancePerStep*$detail['totalStep']);
                $StepsStatList['UserList'][$key]['company_name'] = $companyList[$userList[$detail['user_id']]['company_id']]['company_name']??"未知企业";
                if(!isset($goalList[$userList[$detail['user_id']]['company_id']]))
                {
                    $companyDetail = json_decode($companyList[$userList[$detail['user_id']]['company_id']]['detail'],true);
                    $goalList[$userList[$detail['user_id']]['company_id']] = ($companyDetail['daily_step']??5000)*$days;
                }
                $StepsStatList['UserList'][$key]['achive'] = $detail['totalStep']>=$goalList[$userList[$detail['user_id']]['company_id']]?1:0;
                $StepsStatList['UserList'][$key]['achive_rate'] = intval(100*($detail['totalStep']/$goalList[$userList[$detail['user_id']]['company_id']]));
            }
            //翻页参数
            $page_url = Base_Common::getUrl('',$this->ctl,'stat',$params)."&Page=~page~";
            $page_content =  base_common::multi($StepsStatList['UserCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
            //渲染模版
            include $this->tpl('Hj_Steps_Stat');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
