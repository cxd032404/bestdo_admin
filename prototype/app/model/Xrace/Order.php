<?php
/**
 * 订单管理相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Order extends Base_Widget
{
	//声明所用到的表
	protected $table = 'hs_order';
	protected $table_detail = 'hs_order_detail';

	//支付状态列表
	protected $PayStatusList = array('0'=>"未支付","1"=>"已支付","2"=>"待确认");
	//获取支付状态列表
	public function getPayStatusList()
	{
		return $this->PayStatusList;
	}

	//取消状态列表
	protected $CancelStatusList = array('0'=>"未取消","1"=>"已取消");
	//获取取消状态列表
	public function getCancelStatusList()
	{
		return $this->CancelStatusList;
	}
	/**
	 * 获取单条记录
	 * @param integer $OrderId
	 * @param string $fields
	 * @return array
	 */
	public function getOrder($OrderId, $fields = '*')
	{
		$OrderId = trim($OrderId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $OrderId);
	}
	/**
	 * 获取单条订单的详情记录
	 * @param integer $OrderId
	 * @param string $fields
	 * @return array
	 */
	public function getOrderDetailList($OrderId, $fields = '*')
	{
		$OrderId = trim($OrderId);
		$table_to_process = Base_Widget::getDbTable($this->table_detail);
		return $this->db->select($table_to_process, $fields, '`order_id` = ?', $OrderId);
	}
	//获取主订单记录
	public function getOrderList($params,$fields = array('*'))
	{
		//生成查询列
		$fields = Base_common::getSqlFields($fields);
		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table);
		//获得赛事ID
		$whereCatalog = (isset($params['RaceCatalogId']) && $params['RaceCatalogId']>0)?" active_id = ".$params['RaceCatalogId']." ":"";
		//获得订单ID
		$whereOrder = (isset($params['OrderId']) && strlen($params['OrderId']))?" order_no like '%".$params['OrderId']."%' ":"";
		//获得支付ID
		$whereTrade = isset($params['TradeId'])?" order_no like '%".$params['TradeId']."%' ":"";
		//获得用户ID
		$whereUser = isset($params['UserList'])?" member_id in ".$params['UserList']." ":"";
		//是否已经支付
		$whereIsPay = (isset($params['IsPay'])&& isset($this->PayStatusList[$params['IsPay']]))?" isPay = ".$params['IsPay']." ":"";
		//是否已经支付
		$whereIsCancel = (isset($params['IsCancel'])&&$params['IsCancel']!=-1)?" isCancel = ".$params['IsCancel']." ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereOrder,$whereTrade,$whereUser,$whereIsPay,$whereIsCancel,$whereCatalog);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//获取用户数量
		if(isset($params['getCount'])&&$params['getCount']==1)
		{
			$OrderCount = $this->getOrderCount($params);
		}
		else
		{
			$OrderCount = 0;
		}
		$limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
		$order = " ORDER BY order_no desc";
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
		$return = $this->db->getAll($sql);
		$OrderList = array('OrderList'=>array(),'OrderCount'=>$OrderCount);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$OrderList['OrderList'][$value['id']] = $value;
			}
		}
		else
		{
			return $OrderList;
		}
		return $OrderList;
	}
	//获取主订单记录
	public function getOrderCount($params,$fields = array('*'))
	{
		//生成查询列
		$fields = Base_common::getSqlFields(array("OrderCount"=>"count(order_no)"));
		//获取需要用到的表名
		$table_to_process = Base_Widget::getDbTable($this->table);
		//获得赛事ID
		$whereCatalog = (isset($params['RaceCatalogId']) && $params['RaceCatalogId']>0)?" active_id = ".$params['RaceCatalogId']." ":"";
		//获得订单ID
		$whereOrder = (isset($params['OrderId']) && strlen($params['OrderId']))?" order_no like '%".$params['OrderId']."%' ":"";
		//获得支付ID
		$whereTrade = isset($params['TradeId'])?" order_no like '%".$params['TradeId']."%' ":"";
		//获得用户ID
		$whereUser = isset($params['UserList'])?" member_id in ".$params['UserList']." ":"";
		//是否已经支付
		$whereIsPay = (isset($params['IsPay'])&& isset($this->PayStatusList[$params['IsPay']]))?" isPay = ".$params['IsPay']." ":"";
		//是否已经支付
		$whereIsCancel = (isset($params['IsCancel'])&&$params['IsCancel']!=-1)?" isCancel = ".$params['IsCancel']." ":"";
		//所有查询条件置入数组
		$whereCondition = array($whereOrder,$whereTrade,$whereUser,$whereIsPay,$whereIsCancel,$whereCatalog);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
		return $this->db->getOne($sql);
	}
}
