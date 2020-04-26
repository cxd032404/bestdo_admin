<?php
/**
 * 积分管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_CreditController extends AbstractController
{
	/**商品:Credit
	 * @var string
	 */
	protected $sign = '?ctl=xrace/credit';
	/**
	 * game对象
	 * @var object
	 */
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
		$this->oCredit = new Xrace_Credit();
		$this->oRace = new Xrace_Race();
	}
	//积分列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//对应赛事ID
			$RaceCatalogId = isset($this->request->RaceCatalogId)?intval($this->request->RaceCatalogId):0;
			//获取赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//获取积分列表
			$CreditArr = $this->oCredit->getCreditList($RaceCatalogId);
			//初始空的积分列表
			$CreditList = array();
			//循环积分列表
			foreach($CreditArr as $CreditId => $CreditInfo)
			{
				//获取积分信息
				$CreditList[$CreditInfo['RaceCatalogId']]['CreditList'][$CreditId] = $CreditInfo;
				//计算积分数量
				$CreditList[$CreditInfo['RaceCatalogId']]['CreditCount'] = isset($CreditList[$CreditInfo['RaceCatalogId']]['CreditCount'])?$CreditList[$CreditInfo['RaceCatalogId']]['CreditCount']+1:1;
				//如果对应赛事有配置
				if(isset($RaceCatalogList[$CreditInfo['RaceCatalogId']]))
				{
					//获取赛事名称
					$CreditList[$CreditInfo['RaceCatalogId']]['RaceCatalogName'] = $RaceCatalogList[$CreditInfo['RaceCatalogId']]['RaceCatalogName'];
				}
				else
				{
					$CreditList[$CreditInfo['RaceCatalogId']]['RaceCatalogName'] = 	"未定义";
				}
			}
			//模版渲染
			include $this->tpl('Xrace_Credit_CreditList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加积分配置填写配置页面
	public function creditAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("CreditInsert");
		if($PermissionCheck['return'])
		{
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//渲染模板
			include $this->tpl('Xrace_Credit_CreditAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新积分
	public function creditInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("CreditInsert");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind=$this->request->from('CreditName','RaceCatalogId','CreditRate');
			//积分名称不能为空
			if(trim($bind['CreditName'])=="")
			{
				$response = array('errno' => 1);
			}
			//必须选择一个赛事
			elseif(intval($bind['RaceCatalogId'])==0)
			{
				$response = array('errno' => 2);
			}
			else
			{
                //消费比例强制取正整数
                $bind['CreditRate'] = abs(intval($bind['CreditRate']));
				$res = $this->oCredit->insertCredit($bind);
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
	//修改积分页面
	public function creditModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("CreditModify");
		if($PermissionCheck['return'])
		{
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//积分ID
			$CreditId = intval($this->request->CreditId);
			//获取积分信息
			$CreditInfo = $this->oCredit->getCredit($CreditId,'*');
			//渲染模板
			include $this->tpl('Xrace_Credit_CreditModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新积分
	public function creditUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("CreditModify");
		if($PermissionCheck['return'])
		{

			//获取页面参数
			$bind=$this->request->from('CreditId','CreditName','RaceCatalogId',"CreditRate");
			//积分名称不能为空
			if(trim($bind['CreditName'])=="")
			{
				$response = array('errno' => 1);
			}
			//必须选择一个赛事
			elseif(intval($bind['RaceCatalogId'])==0)
			{
				$response = array('errno' => 2);
			}
			else
			{
				//消费比例强制取正整数
			    $bind['CreditRate'] = abs(intval($bind['CreditRate']));
			    $res = $this->oCredit->updateCredit($bind['CreditId'],$bind);
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
	//删除积分信息
	public function creditDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("CreditDelete");
		if($PermissionCheck['return'])
		{
			$CreditId = trim($this->request->CreditId);
			$this->oCredit->deleteCredit($CreditId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //根据赛事获取积分类目列表
    public function getCreditListByCatalogAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("CreditModify");
        if($PermissionCheck['return'])
        {
            //赛事ID
            $RaceCatalogId = intval($this->request->RaceCatalogId);
            //获取积分列表
            $CreditList = $RaceCatalogId>0?$this->oCredit->getCreditList($RaceCatalogId,"CreditId,CreditName"):array();
            //循环积分列表
            foreach($CreditList as $CreditId => $CreditInfo)
            {
                echo  "<option value= '".$CreditId."'>".$CreditInfo['CreditName']."</option>";
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
    //根据赛事获取积分类目列表
    public function getFrequencyConditionAction()
    {
        //检查权限
        $PermissionCheck = $this->manager->checkMenuPermission("CreditModify");
        if($PermissionCheck['return'])
        {
            $this->oAction = new Xrace_Action();
            //频率
            $Frequence = trim(urldecode($this->request->Frequence));
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
            //获取可选频率列表
            $CreditFrequenceList  = $this->oCredit->getCreditFrequenceList();
            if(isset($CreditFrequenceList[$Frequence]))
            {
                if($Credit['Frequency']==$Frequence)
                {
                    $params = $Credit['ParamList'];
                }
                elseif($Frequence == "dateRange")
                {
                    $params['StartDate'] = date("Y-m-d", time());
                    $params['EndDate'] = date('Y-m-d', time() + 86400);
                }
                else
                {
                    $params = array();
                }
                $ConditionText = $this->oCredit->parthFrequenceConditioToHtml($CreditFrequenceList[$Frequence],$params);
                echo $ConditionText;
            }
        }
        else
        {
            $home = $this->sign;
            include $this->tpl('403');
        }
    }
}
