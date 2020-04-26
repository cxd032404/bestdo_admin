<?php
/**
 * 动作管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_ActionController extends AbstractController
{
	/**商品:Action
	 * @var string
	 */
	protected $sign = '?ctl=xrace/action';
	/**
	 * game对象
	 * @var object
	 */
	protected $oAction;
    protected $oCredit;
    protected $oRace;
	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oAction = new Xrace_Action();
        $this->oRace = new Xrace_Race();
        $this->oCredit = new Xrace_Credit();
    }
	//动作列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//获取动作列表
            $ActionList = $this->oAction->getActionList();
			//循环动作列表
			foreach($ActionList as $ActionId => $ActionInfo)
			{
			    //解包积分列表
			    $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
                //转化为文本
                $ActionList[$ActionId]['CreditListHtml'] = $this->oCredit->ParthCreditListToHtml($ActionInfo['CreditList'],$ActionId,1,1);
			}
			//模版渲染
			include $this->tpl('Xrace_Action_ActionList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加产品配置填写配置页面
	public function actionAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ActionInsert");
		if($PermissionCheck['return'])
		{
			//渲染模板
			include $this->tpl('Xrace_Action_ActionAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新动作
	public function actionInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ActionInsert");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind = $this->request->from('ActionName','Action');
			//动作名称不能为空
			if(trim($bind['ActionName'])=="")
			{
				$response = array('errno' => 1);
			}
			//动作标识不能为空
			elseif(trim($bind['Action'])=="")
			{
				$response = array('errno' => 2);
			}
			else
			{
				//根据新的标识获取动作信息
			    $ActionInfo = $this->oAction->getActionByAction($bind['Action'],"ActionId");
                //如果获取到
                if($ActionInfo['ActionId'])
                {
                    $response = array('errno' => 3);
                }
                else
                {
                    $res = $this->oAction->insertAction($bind);
                    $response = $res ? array('errno' => 0) : array('errno' => 9);
                }
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改动作页面
	public function actionModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
		if($PermissionCheck['return'])
		{
			//动作ID
			$ActionId = intval($this->request->ActionId);
			//获取动作信息
			$ActionInfo = $this->oAction->getAction($ActionId,'*');
            //解包积分列表
            $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
            //转变为文本
            $CreditListHtml = $this->oCredit->ParthCreditListToHtml($ActionInfo['CreditList'],$ActionId,1,1);
            //渲染模板
			include $this->tpl('Xrace_Action_ActionModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新动作
	public function actionUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
		if($PermissionCheck['return'])
		{

			//获取页面参数
			$bind = $this->request->from('ActionId','ActionName','Action');
            //动作名称不能为空
            if(trim($bind['ActionName'])=="")
            {
                $response = array('errno' => 1);
            }
            //动作标识不能为空
            elseif(trim($bind['Action'])=="")
            {
                $response = array('errno' => 2);
            }
			else
			{
				$res = $this->oAction->updateAction($bind['ActionId'],$bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除动作信息
	public function actionDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ActionDelete");
		if($PermissionCheck['return'])
		{
			//动作ID
		    $ActionId = intval($this->request->ActionId);
			//删除动作
            $this->oAction->deleteAction($ActionId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //添加用户动作的积分类目填写配置页面
    public function actionCreditAddAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
        if($PermissionCheck['return'])
        {
            //动作ID
            $ActionId = intval($this->request->ActionId);
            //动作信息
            $ActionInfo = $this->oAction->getAction($ActionId,'*');
            //获取赛事列表
            $RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"RaceCatalogId,RaceCatalogName",0);
            //获取可选频率列表
            $CreditFrequenceList  = $this->oCredit->getCreditFrequenceList();
            //模板渲染
            include $this->tpl('Xrace_Action_ActionCreditAdd');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改用户动作的积分类目填写配置页面
    public function actionCreditModifyAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
        if($PermissionCheck['return'])
        {
            //动作ID
            $ActionId = intval($this->request->ActionId);
            //积分ID
            $CId = intval($this->request->CId);
            //动作信息
            $ActionInfo = $this->oAction->getAction($ActionId,'*');
            //积分数组解包
            $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
            //获取当前选中的积分配置
            $Credit = $ActionInfo['CreditList'][$CId];
            //获取积分信息
            $CreditInfo = $this->oCredit->getCredit($Credit['CreditId'],"RaceCatalogId,CreditId");
            //获取积分列表
            $CreditList = $this->oCredit->getCreditList($CreditInfo['RaceCatalogId'],"CreditId,CreditName");
            //获取赛事列表
            $RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"RaceCatalogId,RaceCatalogName",0);
            //获取可选频率列表
            $CreditFrequenceList  = $this->oCredit->getCreditFrequenceList();
            $ConditionText = $this->oCredit->parthFrequenceConditioToHtml($CreditFrequenceList[$Credit['Frequency']],$Credit['ParamList']);
            //模板渲染
            include $this->tpl('Xrace_Action_ActionCreditModify');
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //添加新动作
    public function actionCreditInsertAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
        if($PermissionCheck['return'])
        {
            //动作ID
            $ActionId = intval($this->request->ActionId);
            //获取页面参数
            $bind = $this->request->from('CreditId','Credit','CreditCount','Frequency','ParamList','StartTime','EndTime');
            //获取动作信息
            $ActionInfo = $this->oAction->getAction($ActionId,'ActionId,CreditList');
            //积分数组解包
            $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
            //从头开始循环
            for($i=1;$i<=count($ActionInfo['CreditList'])+1;$i++)
            {
                //找到空位
                if(!isset($ActionInfo['CreditList'][$i]))
                {
                    $ActionInfo['CreditList'][$i] = $bind;
                    break;
                }
            }
            //积分数组打包
            $ActionInfo['CreditList'] = json_encode($ActionInfo['CreditList']);
            $res = $this->oAction->updateAction($ActionInfo['ActionId'],$ActionInfo);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            echo json_encode($response);
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改动作
    public function actionCreditUpdateAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
        if($PermissionCheck['return'])
        {
            //动作ID
            $ActionId = intval($this->request->ActionId);
            //积分ID
            $CId = intval($this->request->CId);
            //获取页面参数
            $bind = $this->request->from('CreditId','Credit','CreditCount','Frequency','ParamList','StartTime','EndTime');
            //获取动作信息
            $ActionInfo = $this->oAction->getAction($ActionId,'ActionId,CreditList');
            //积分数组解包
            $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
            //存入新数据
            $ActionInfo['CreditList'][$CId] = $bind;
            //积分数组打包
            $ActionInfo['CreditList'] = json_encode($ActionInfo['CreditList']);
            //更新数据
            $res = $this->oAction->updateAction($ActionInfo['ActionId'],$ActionInfo);
            $response = $res ? array('errno' => 0) : array('errno' => 9);
            echo json_encode($response);
            return true;
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //修改动作
    public function actionCreditDeleteAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("ActionModify");
        if($PermissionCheck['return'])
        {
            //动作ID
            $ActionId = intval($this->request->ActionId);
            //积分ID
            $CId = intval($this->request->CId);
            //获取动作信息
            $ActionInfo = $this->oAction->getAction($ActionId,'ActionId,CreditList');
            //积分数组解包
            $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
            //删除数据
            unset($ActionInfo['CreditList'][$CId]);
            //积分数组打包
            $ActionInfo['CreditList'] = json_encode($ActionInfo['CreditList']);
            //更新数据
            $this->oAction->updateAction($ActionInfo['ActionId'],$ActionInfo);
            $this->response->goBack();
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
