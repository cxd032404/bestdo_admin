<?php
/**用户管理*/

class Xrace_OrderController extends AbstractController
{
	/**用户管理相关:Order
	 * @var string
	 */
	protected $sign = '?ctl=xrace/order';
	/**
	 * game对象
	 * @var object
	 */
	protected $oOrder;
	protected $oUser;
	protected $oRace;

        /**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oOrder = new Xrace_Order();
		$this->oUser = new Xrace_User();
		$this->oRace = new Xrace_Race();
	}
	//订单列表
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("OrderList");
		if($PermissionCheck['return'])
		{
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,'RaceCatalogName,RaceCatalogId',0);
			//获取支付状态
			$PayStatusList = $this->oOrder->getPayStatusList();
			//获取取消状态
			$CancelStatusList = $this->oOrder->getCancelStatusList();
			//页面参数预处理
			//赛事ID
			$params['RaceCatalogId'] = isset($this->request->RaceCatalogId)?intval($this->request->RaceCatalogId):0;
			$params['IsPay'] = isset($PayStatusList[intval($this->request->IsPay)])?intval($this->request->IsPay):-1;
			$params['IsCancel'] = isset($CancelStatusList[intval($this->request->IsCancel)])?intval($this->request->IsCancel):-1;
			$params['OrderId'] = urldecode(trim($this->request->OrderId))?substr(urldecode(trim($this->request->OrderId)),0,30):"";
			$params['PayId'] = urldecode(trim($this->request->PayId))?substr(urldecode(trim($this->request->PayId)),0,30):"";
			$params['Name'] = urldecode(trim($this->request->Name))?substr(urldecode(trim($this->request->Name)),0,30):"";
			//分页参数
			$params['Page'] = abs(intval($this->request->Page))?abs(intval($this->request->Page)):1;
			$params['PageSize'] = 20;
			//获取用户列表时需要获得记录总数
			$params['getCount'] = 1;
			//如果有输入姓名
			if(strlen($params['Name']))
			{
				//模糊查询用户列表
				$UserList = $this->oUser->getUserList(array('Name'=>$params['Name'],'getCount'=>0),array("UserId"));
				//如果有查找到用户
				if(count($UserList['UserList']))
				{
					//生成用户ID列表
					$params['UserList'] = "(".implode(",",array_keys($UserList['UserList'])).")";
				}
				else
				{
					$params['UserList'] = "(0)";
				}
			}
			//获取用户列表
			$OrderList = $this->oOrder->getOrderList($params);
			//导出EXCEL链接
			$export_var = "<a href =".(Base_Common::getUrl('','xrace/user','user.list.download',$params))."><导出表格></a>";
			//翻页参数
			$page_url = Base_Common::getUrl('','xrace/order','index',$params)."&Page=~page~";
			$page_content =  base_common::multi($OrderList['OrderCount'], $page_url, $params['Page'], $params['PageSize'], 10, $maxpage = 100, $prevWord = '上一页', $nextWord = '下一页');
			//初始化空的用户列表
			$UserList = array();
			foreach($OrderList['OrderList'] as $OrderId => $OrderInfo)
			{
				//如果用户数据尚未获取
				if(!isset($UserList[$OrderInfo['member_id']]))
				{
					//获取用户数据
					$UserInfo = $this->oUser->getUserInfo($OrderInfo['member_id'],"UserId,name");
					//如果获取到用户数据
					if(isset($UserInfo['UserId']))
					{
						//保存到用户列表
						$UserList[$UserInfo['UserId']] = $UserInfo;
					}
				}
				//获取用户姓名
				$OrderList['OrderList'][$OrderId]['Name'] = isset($UserList[$OrderInfo['member_id']])?$UserList[$OrderInfo['member_id']]['name']:"未知用户";
				//获取订单支付状态
				$OrderList['OrderList'][$OrderId]['PayStatusName'] = isset($PayStatusList[$OrderInfo['isPay']])?$PayStatusList[$OrderInfo['isPay']]:"未定义";
				//获取订单取消状态
				$OrderList['OrderList'][$OrderId]['CancelStatusName'] = isset($CancelStatusList[$OrderInfo['isCancel']])?$CancelStatusList[$OrderInfo['isCancel']]:"未定义";
				//获取订单取消状态
				$OrderList['OrderList'][$OrderId]['RaceCatalogName'] = isset($RaceCatalogList[$OrderInfo['active_id']])?$RaceCatalogList[$OrderInfo['active_id']]['RaceCatalogName']:"未定义";
			}
			//模板渲染
			include $this->tpl('Xrace_Order_OrderList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//用户列表下载
	public function orderListDownloadAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("OrderListDownload");
		if($PermissionCheck['return'])
		{
			//获取性别列表
			$SexList = $this->oOrder->getSexList();
			//获取实名认证状态列表
			$AuthStatusList = $this->oOrder->getAuthStatus();
			//获取实名认证证件类型列表
			$AuthIdTypesList = $this->oOrder->getAuthIdType();

			//页面参数预处理
			$params['Sex'] = isset($SexList[intval($this->request->Sex)])?intval($this->request->Sex):0;
			$params['Name'] = urldecode(trim($this->request->Name))?substr(urldecode(trim($this->request->Name)),0,8):"";
			$params['NickName'] = urldecode(trim($this->request->NickName))?substr(urldecode(trim($this->request->NickName)),0,8):"";
			$params['AuthStatus'] = isset($AuthStatusList[$this->request->AuthStatus])?intval($this->request->AuthStatus):-1;

			//分页参数
			$params['PageSize'] = 500;

			$oExcel = new Third_Excel();
			$FileName= ($this->manager->name().'用户列表');
			$oExcel->download($FileName)->addSheet('用户');
			//标题栏
			$title = array("用户ID","微信openId","姓名","昵称","性别","出生年月","实名认证状态");
			$oExcel->addRows(array($title));
			$Count = 1;$params['Page'] =1;
			do
			{
				$OrderList = $this->oOrder->getOrderList($params);
				$Count = count($OrderList['OrderList']);
				foreach($OrderList['OrderList'] as $OrderId => $OrderInfo)
				{
					//生成单行数据
					$t = array();
					$t['UserId'] = $OrderInfo['UserId'];
					$t['open_wx_id'] = $OrderInfo['wx_open_id'];
					$t['open_wx_id'] = $OrderInfo['wx_open_id'];
					$t['name'] = $OrderInfo['name'];
					$t['nick_name'] = $OrderInfo['nick_name'];
					$t['sex'] = isset($SexList[$OrderInfo['sex']])?$SexList[$OrderInfo['sex']]:"保密";
					$t['AuthStatus'] = isset($AuthStatusList[$OrderInfo['auth_state']])?$AuthStatusList[$OrderInfo['auth_state']]:"未知";

					$oExcel->addRows(array($t));
					unset($t);
				}
				$params['Page']++;
				$oExcel->closeSheet()->close();
			}
			while($Count>0);
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//用户详情
	public function orderDetailAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("OrderList");
		if($PermissionCheck['return'])
		{
			//获取支付状态
			$PayStatusList = $this->oOrder->getPayStatusList();
			//获取取消状态
			$CancelStatusList = $this->oOrder->getCancelStatusList();
			//订单号
			$OrderId = trim($this->request->OrderId);
			//获取订单号信息
			$OrderInfo = $this->oOrder->getOrder($OrderId);
			//获取赛事信息
			$RaceCatalogInfo = $this->oRace->getRaceCatalog($OrderInfo['active_id'],'*',0);
			//获取用户数据
			$UserInfo = $this->oUser->getUserInfo($OrderInfo['member_id'],"UserId,name");
			//获取用户姓名
			$OrderInfo['Name'] = isset($UserInfo['UserId'])?$UserInfo['name']:"未知用户";
			//获取订单支付状态
			$OrderInfo['PayStatusName'] = isset($PayStatusList[$OrderInfo['isPay']])?$PayStatusList[$OrderInfo['isPay']]:"未定义";
			//获取订单取消状态
			$OrderInfo['CancelStatusName'] = isset($CancelStatusList[$OrderInfo['isCancel']])?$CancelStatusList[$OrderInfo['isCancel']]:"未定义";
			//获取订单取消状态
			$OrderInfo['RaceCatalogName'] = isset($RaceCatalogInfo['RaceCatalogId'])?$RaceCatalogInfo['RaceCatalogName']:"未定义";
			//获取子订单信息
			$OrderDetailList = $this->oOrder->getOrderDetailList($OrderInfo['id']);
			//如果有获取到子订单
			if(count($OrderDetailList))
			{
				//循环订单详情
				foreach($OrderDetailList as $LogId => $OrderDetailInfo)
				{
					if($OrderDetailInfo['product_id']>0)
					{
						$OrderDetailList[$LogId]['OrderType'] = "产品购买";
					}
					else
					{
						$OrderDetailList[$LogId]['OrderType'] = "比赛报名";
					}
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Order_OrderDetail');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}
