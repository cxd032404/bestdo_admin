<?php
/**
 * 积分相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Credit extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_credit';
    protected $table_credit_update_log = 'user_credit_log';
    protected $table_credit_update_log_total = 'user_credit_log_total';
    protected $table_credit_user = 'user_credit_sum';

    protected $creditFrequenceList = array("day"=>array("Name"=>"每日","ParamList"=>array()),
        "week"=>array("Name"=>"每周","ParamList"=>array()),
        "month"=>array("Name"=>"每月","ParamList"=>array()),
        "year"=>array("Name"=>"每年","ParamList"=>array()),
        "total"=>array("Name"=>"总计","ParamList"=>array()));
        //"dateRange"=>array("Name"=>"日期","ParamList"=>array("StartDate"=>array("Name"=>"开始日期","Type"=>"Date"),"EndDate"=>array("Name"=>"结束日期","Type"=>"Date"))));
    public function getCreditFrequenceList()
    {
        return $this->creditFrequenceList;
    }
	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getCreditList($RaceCatalogId = 0,$fields = "*")
	{
		$RaceCatalogId = intval($RaceCatalogId);
		//初始化查询条件
		$whereCatalog = ($RaceCatalogId != 0)?" RaceCatalogId = $RaceCatalogId":"";
		$whereCondition = array($whereCatalog);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY CreditId ASC";
		$return = $this->db->getAll($sql);
		$CreditList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$CreditList[$value['CreditId']] = $value;
			}
		}
		return $CreditList;
	}
	/**
	 * 获取单条记录
	 * @param integer $AppId
	 * @param string $fields
	 * @return array
	 */
	public function getCredit($CreditId, $fields = '*')
	{
	    $CreditId = intval($CreditId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`CreditId` = ?', $CreditId);
	}
	/**
	 * 更新
	 * @param integer $AppId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateCredit($CreditId, array $bind)
	{
		$CreditId = intval($CreditId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`CreditId` = ?', $CreditId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertCredit(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $AppId
	 * @return boolean
	 */
	public function deleteCredit($CreditId)
	{
		$CreditId = intval($CreditId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`CreditId` = ?', $CreditId);
	}
    //把积分获取规则转化成HTML
    public function ParthCreditListToHtml($CreditList,$ActionId,$modify=0,$delete=0)
    {
        //如果已配置积分列表
        if (count($CreditList)) {
            //获得积分频率列表
            $CreditFrequenceList = $this->getCreditFrequenceList();
            //循环积分列表
            foreach ($CreditList as $key => $Credit)
            {
                //获取积分信息
                $CreditInfo = $this->getCredit($Credit['CreditId'], "CreditId,CreditName");
                if (!count($Credit['ParamList']))
                {
                    $t = $Credit['StartTime']."~".$Credit['EndTime']." ".$CreditFrequenceList[$Credit['Frequency']]['Name'];
                }
                else
                {
                    $t2 = array();
                    foreach ($Credit['ParamList'] as $k => $Param)
                    {
                        $t2[$k] = $CreditFrequenceList[$Credit['Frequency']]["ParamList"][$k]['Name'] . ":" . $Param;
                    }
                    $t = $Credit['StartTime']."~".$Credit['EndTime']." ".("" . implode(",", $t2) . "");
                }
                $t .= "<br>" . "每次获得：" . $CreditInfo['CreditName'] . " " . $Credit['Credit'] . "/次数：" . ($Credit['CreditCount'] > 0 ? ($Credit['CreditCount'] . "次") : "不限");
                $text[$key] = $t;
                if ($modify) {
                    $text[$key] .= '<a href="javascript:void(0);" onclick="CreditModify(' . "'" . $ActionId . "'" . ',' . "'" . $key . "'" . ')"> 修改 </a>';
                }
                if ($delete) {
                    $text[$key] .= '<a href="javascript:void(0);" onclick="CreditDelete(' . "'" . $ActionId . "'" . ',' . "'" . $key . "'" . ',' . "'" . $CreditInfo['CreditName'] . "'" . ')"> 删除 </a>';
                }
            }
            return ("" . implode("<br>", $text) . "");
        }
    }
    //把积分获取频率转换成HTML
    public function parthFrequenceConditioToHtml($CreditFrequence,$params)
    {
        $t = array();
        foreach($CreditFrequence['ParamList'] as $k => $ParamInfo)
        {
            switch ($ParamInfo['Type'])
            {
                case "Date":
                    $t[$k] = $ParamInfo['Name']." ".'<input type="text" class="span2" name="ParamList['.$k.']" value="' . $params[$k] . '" class="input-medium" onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:' . "'yyyy-MM-dd'" . '})">';
            }
        }
        return implode(" ",$t);
    }
    public function Credit($CreditInfo,$params,$UserId,$Status = 1)
    {
        //检查是否已经领取上限
        $Check = $this->checkCreditFrequency($UserId,$CreditInfo,$params);
        if(!$Check)
        {
            return false;
        }
        //获取当前时间
        $Time = date("Y-m-d H:i:s",time());
        //事务开始
        $this->db->begin();
        //计算所在用户分表的后缀
        $suffix = substr(md5($UserId),0,1);
        //检测用户积分变更表是否存在
        $table_user_log = $this->db->createTable($this->table_credit_update_log,$suffix);
        //检测用户积分汇总表是否存在
        $table_user = $this->db->createTable($this->table_credit_user,$suffix);
        //检测总表
        $table_total = Base_Widget::getDbTable($this->table_credit_update_log_total);
        //积分变更表的数据
        $bindLog = array("UserId"=>$UserId,"CreditId"=>$CreditInfo['CreditId'],"Time"=>$Time,"Credit"=>$CreditInfo['Credit']);
        //循环传入的参数列表
        foreach($params as $key => $value)
        {
            //依次赋值
            $bindLog[$key] = $value;
        }
        //积分表的变更数据
        $bindUpdate = array("LastUpdateTime"=>$Time,"Credit"=>"_Credit".($CreditInfo['Credit']>0?("+".$CreditInfo['Credit']):$CreditInfo['Credit']));
        //积分表的新增数据
        $bind = array("LastUpdateTime"=>$Time,"UserId"=>$UserId,"CreditId"=>$CreditInfo['CreditId'],"Credit"=>$CreditInfo['Credit']);
        //更新汇总表
        $CreditSum = $this->db->insert_update($table_user,$bind,$bindUpdate);
        //新增记录表
        $CreditLog = $this->db->insert($table_user_log, $bindLog);
        //组合新的ID
        $bindLog['Id'] = $suffix."_".$CreditLog;
        //新增记录表
        $CreditLogTotal = $this->db->insert($table_total, $bindLog);
        //如果同时成功
        if($CreditSum && $CreditLog && $CreditLogTotal)
        {
            //提交
            $this->db->commit();
            return true;
        }
        else
        {
            //回滚
            $this->db->rollBack();
            return false;
        }
    }
    /**
     * 获取积分总表的积分详情
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getCreditLog($params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        if(isset($params['UserId'])&&$params['UserId']>0)
        {
            //计算所在用户分表的后缀
            $suffix = substr(md5($params['UserId']),0,1);
            //检测用户积分变更表是否存在
            $table_to_process = $this->db->createTable($this->table_credit_update_log,$suffix);
            //用户
            $whereUser = " UserId = '".$params['UserId']."' ";
        }
        else
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table_credit_update_log_total);
        }
        //动作
        $whereAction = (isset($params['ActionId']) && $params['ActionId']>0)?" ActionId = '".$params['ActionId']."' ":"";
        //积分
        $whereCredit = (isset($params['CreditId']) && $params['CreditId']>0)?" CreditId = '".$params['CreditId']."' ":"";
        //比赛
        $whereRace = (isset($params['RaceId']) && $params['RaceId']>0)?" RaceId = '".$params['RaceId']."' ":"";
        //开始时间
        $whereStartTime = isset($params['StartTime'])?" Time >= '".$params['StartTime']."' ":"";
        //结束时间
        $whereEndTime = isset($params['EndTime'])?" Time <= '".$params['EndTime']."' ":"";
        //订单
        $whereOrder = (isset($params['OrderId']) && $params['OrderId']!="")?" OrderId = '".$params['OrderId']."' ":"";
        //订单
        $whereUserApplied = (isset($params['UserApplied']) && $params['UserApplied']!=0)?" UserRaceId = '".$params['OrderId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereAction,$whereCredit,$whereRace,$whereUser,$whereStartTime,$whereEndTime,$whereOrder);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $CreditLogCount = $this->getCreditLogCount($params);
        }
        else
        {
            $CreditLogCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $order = " ORDER BY Time desc";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
        $return = $this->db->getAll($sql);
        $CreditLog = array('CreditLog'=>array(),'CreditLogCount'=>$CreditLogCount,'TotalPage' => ceil($CreditLogCount/$params['PageSize']));
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $CreditLog['CreditLog'][$value['Id']] = $value;
            }
        }
        else
        {
            return $CreditLog;
        }
        return $CreditLog;
    }
    /**
     * 获取用户积分记录数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getCreditLogCount($params)
    {
        //生成查询列
        $fields = Base_common::getSqlFields(array("CreditLogCount"=>"count(Id)"));
        if(isset($params['UserId'])&&$params['UserId']>0)
        {
            //计算所在用户分表的后缀
            $suffix = substr(md5($params['UserId']),0,1);
            //检测用户积分变更表是否存在
            $table_to_process = $this->db->createTable($this->table_credit_update_log,$suffix);
            //用户
            $whereUser = " UserId = '".$params['UserId']."' ";
        }
        else
        {
            //获取需要用到的表名
            $table_to_process = Base_Widget::getDbTable($this->table_credit_update_log_total);
        }
        //动作
        $whereAction = (isset($params['ActionId']) && $params['ActionId']>0)?" ActionId = '".$params['ActionId']."' ":"";
        //积分
        $whereCredit = (isset($params['CreditId']) && $params['CreditId']>0)?" CreditId = '".$params['CreditId']."' ":"";
        //比赛
        $whereRace = (isset($params['RaceId']) && $params['RaceId']>0)?" RaceId = '".$params['RaceId']."' ":"";
        //开始时间
        $whereStartTime = isset($params['StartTime'])?" Time >= '".$params['StartTime']."' ":"";
        //结束时间
        $whereEndTime = isset($params['EndTime'])?" Time <= '".$params['EndTime']."' ":"";
        //订单
        $whereOrder = (isset($params['OrderId']) && $params['OrderId']!="")?" OrderId = '".$params['OrderId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereAction,$whereCredit,$whereRace,$whereUser,$whereStartTime,$whereEndTime,$whereOrder);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取用户积分总表的积分详情
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getUserCredit($UserId,$params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //计算所在用户分表的后缀
        $suffix = substr(md5($UserId),0,1);
        //检测用户积分变更表是否存在
        $table_to_process = $this->db->createTable($this->table_credit_user,$suffix);
        //用户
        $whereUser = " UserId = '".$UserId."' ";
        //所有查询条件置入数组
        $whereCondition = array($whereUser);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $order = " ORDER BY CreditId desc";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
        $return = $this->db->getAll($sql);
        $CreditList = array('CreditList'=>array());
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $CreditList['CreditList'][$value['CreditId']] = $value;
            }
        }
        else
        {
            return $CreditList;
        }
        return $CreditList;
    }
    //检查用户在指定的积分获取频率中是否超标
    public function checkCreditFrequency($UserId,$CreditInfo,$params)
    {
        if(strtotime($CreditInfo['StartTime'])>0)
        {
            $Time = time();
            if(($Time < strtotime($CreditInfo['StartTime'])) || ($Time > strtotime($CreditInfo['EndTime'])))
            {
                return false;
            }
        }
        if(isset($CreditInfo['Frequency']))
        {
            //计算要检查的时间范围
            $TimeRange = base_common::getFrequencyTimeRange(time(),$CreditInfo['Frequency']);
            //计算参数列表
            $FrequencyParams = array("UserId"=>$UserId,"CreditId"=>$CreditInfo['CreditId'],"StartTime"=>$TimeRange['StartTime'],"EndTime"=>$TimeRange['EndTime']);
            //拼接其他参数
            $FrequencyParams = array_merge($FrequencyParams,$params);
            //获取积分获取记录
            $CreditCount = $this->getCreditLogCount($FrequencyParams);
            //检查是否超限
            if($CreditCount>=$CreditInfo['CreditCount'])
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    //根据订单兑换积分抵扣
    public function exchangeCreditByOrder($UserId,$OrderId,$ExchangeAmount,$CreditStack)
    {
        //先回滚已有的订单记录
        $this->exchangeCreditByOrderRevert($UserId,$OrderId);
        //总的区块数
        $StackCount = intval($ExchangeAmount/$CreditStack);
        //实际兑换数量
        $ExchangeArray = array("Amount" => 0,"ExchangeCreditList"=>array());
        //选择可兑换的积分
        $params = array("Money"=>1);
        //结果数组 如果列表中有数据则返回成功，否则返回失败
        $UserCreditList = $this->getUserCredit($UserId,$params);
        //循环用户的积分列表
        foreach($UserCreditList['CreditList'] as $CreditId => $Credit)
        {
            //重新获取积分信息
            $CreditInfo = $this->getCredit($CreditId,"CreditId,CreditName,CreditRate");
            //如果获取到
            if (isset($CreditInfo['CreditId']))
            {
                //如果可以队员
                if($CreditInfo['CreditRate']>0)
                {
                    //每个区块对应的积分数量
                    $Count = intval($CreditStack/$CreditInfo['CreditRate']);
                    //如果不够
                    if($Count>$Credit['Credit'])
                    {
                        //跳过
                        continue;
                    }
                    else
                    {
                        //需要兑换的区块数量
                        $StackToExhchage = min(intval($Credit['Credit']*$CreditInfo['CreditRate']/$CreditStack),$StackCount);
                        //需要兑换的积分数量
                        $CreditToExchange = $StackToExhchage*$Count;
                        //消费
                        $Exchange = $this->Credit(array("CreditId"=>$CreditId,"Credit"=>-1*$CreditToExchange),array("OrderId"=>$OrderId),$UserId,0);
                        //如果消费成功
                        if($Exchange)
                        {
                            //剩余的区块数量
                            $StackCount = $StackCount-$StackToExhchage;
                            //剩余的待兑换积分数量
                            $ExchangeArray['Amount'] += $CreditToExchange*$CreditInfo['CreditRate'];
                            $ExchangeArray['ExchangeCreditList'][$CreditId] = array("Credit"=>$CreditToExchange,"Amount"=>$CreditToExchange*$CreditInfo['CreditRate']);
                        }
                    }
                }
            }
        }
        return $ExchangeArray;
    }
    //根据订单兑换积分抵扣
    public function exchangeCreditByOrderRevert($UserId,$OrderId)
    {
        $return = array("success"=>0,"CreditList"=>array());
        //获取订单关联记录
        $CreditLog = $this->getCreditLog(array("UserId"=>$UserId,"OrderId"=>$OrderId));
        //循环积分记录
        foreach($CreditLog['CreditLog'] as $LogId => $LogInfo)
        {
            //回滚
            $revert = $this->creditLogRevert($LogInfo['Id'],$UserId);
            //如果回滚成功
            if(isset($revert['CreditId']))
            {
                //成功数量累加
                $return['success'] ++;
                //保存记录
                $return['CreditList'][$revert['CreditId']] = $revert;
            }
        }
        return $return;
    }
    //根据订单兑换积分抵扣
    public function exchangeCreditByOrderConfirm($UserId,$OrderId)
    {
        $return = array("success"=>0,"CreditList"=>array());
        //获取订单关联记录
        $CreditLog = $this->getCreditLog(array("UserId"=>$UserId,"OrderId"=>$OrderId));
        //循环积分记录
        foreach($CreditLog['CreditLog'] as $LogId => $LogInfo)
        {
            //回滚
            $Confirm = $this->creditLogConfirm($LogInfo['Id'],$UserId);
            //如果回滚成功
            if(isset($revert['CreditId']))
            {
                //成功数量累加
                $return['success'] ++;
            }
        }
        return $return;
    }
    /**
     * 获取单条记录
     * @param integer $AppId
     * @param string $fields
     * @return array
     */
    public function getCreditLogTotal($TotalId, $fields = '*')
    {
        $TotalId = trim($TotalId);
        $table_to_process = Base_Widget::getDbTable($this->table_credit_update_log_total);
        return $this->db->selectRow($table_to_process, $fields, '`Id` = ?', $TotalId);
    }
    public function creditLogRevert($Id,$UserId)
    {
        //获取系统当前时间
        $Time = date("Y-m-d H:i:s",time());
        //计算所在用户分表的后缀
        $suffix = substr(md5($UserId),0,1);
        $CreditLog = $this->getCreditLogTotal($suffix."_".$Id);
        if(isset($CreditLog['Id']))
        {
            $this->db->begin();
            //用户积分变更表
            $table_user_log =  Base_Widget::getDbTable($this->table_credit_update_log)."_".$suffix;
            $deleteUserLog = $this->db->delete($table_user_log,'`Id` = ?',$Id);
            //用户积分汇总表
            $table_user = Base_Widget::getDbTable($this->table_credit_user)."_".$suffix;
            //积分表的变更数据
            $bindUpdate = array("LastUpdateTime"=>$Time,"Credit"=>"_Credit+".(-1*$CreditLog['Credit']));
            $updateUserSum = $this->db->update($table_user,$bindUpdate,'`UserId` = ? and `CreditId` = ?', array($UserId,$CreditLog['CreditId']));
            //积分变更总表
            $table_total = Base_Widget::getDbTable($this->table_credit_update_log_total);
            $deleteTotalLog = $this->db->delete($table_total,'`Id` = ?',$suffix."_".$Id);
            if($deleteUserLog && $updateUserSum && $deleteTotalLog)
            {
                $this->db->commit();
                return array("CreditId"=>$CreditLog['CreditId'],"Credit"=>-1*$CreditLog['Credit']);
            }
            else
            {
                $this->db->rollBack();
                return false;
            }
        }
    }
    public function creditLogConfirm($Id,$UserId)
    {
        //获取系统当前时间
        $Time = date("Y-m-d H:i:s",time());
        //计算所在用户分表的后缀
        $suffix = substr(md5($UserId),0,1);
        $CreditLog = $this->getCreditLogTotal($suffix."_".$Id);
        if(isset($CreditLog['Id']))
        {
            $this->db->begin();
            //用户积分变更表
            $bindUpdate = array("Status"=>1);
            $table_user_log =  Base_Widget::getDbTable($this->table_credit_update_log)."_".$suffix;
            $updateUserLog = $this->db->update($table_user_log,$bindUpdate,'`Id` = ?',$Id);
            //积分变更总表
            $table_total = Base_Widget::getDbTable($this->table_credit_update_log_total);
            $updateTotalLog = $this->db->update($table_total,$bindUpdate,'`Id` = ?',$suffix."_".$Id);
            if($updateUserLog && $updateTotalLog)
            {
                $this->db->commit();
                return array("CreditId"=>$CreditLog['CreditId']);
            }
            else
            {
                $this->db->rollBack();
                return false;
            }
        }
    }
}

//delete FROM `user_credit_log_e` WHERE UserId=11524 ;
//delete FROM `user_credit_log_total` WHERE UserId=11524 ;
