<?php
/**
 * 赛事配置相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_AidStation extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_aid_station';
	protected $table_aid_code = 'config_aid_code';
    protected $table_aid_code_type = 'config_aid_code_type';
	protected $AidCodeStatus = array("-1"=>"全部","0"=>"未使用","1"=>"已使用");

	public function getAidCodeStatus()
    {
        return $this->AidCodeStatus;
    }
	//更新单个补给点
	public function updateAidStation($AidStationId, array $bind)
	{
        $AidStationId = intval($AidStationId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`AidStationId` = ?', $AidStationId);
	}
	//添加单个补给点
	public function insertAidStation(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}
	//删除单个补给点
	public function deleteAidStation($AidStationId)
	{
		$AidStationId = intval($AidStationId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`AidStationId` = ?', $AidStationId);
	}
	//根据赛事获取所有组别列表
	//赛事ID为0则获取全部组别
	public function getAidStationIdList($params,$fields = "*")
	{
		//初始化查询条件
        $whereStage = (isset($params['RaceStageId']) && ($params['RaceStageId'] >0))?(" RaceStageId = '".$params['RaceStageId'])."'":"";
		$whereCondition = array($whereStage);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
        $table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . "  where 1 ".$where." ORDER BY RaceStageId desc,AidStationId asc";
		$return = $this->db->getAll($sql);
		$AidStationList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
			    $AidStationList[$value['AidStationId']] = $value;
			}
		}
		return $AidStationList;
	}
	//获取单个赛事组别的信息
	public function getAidStation($AidStationId, $fields = '*')
	{
		$AidStationId = intval($AidStationId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`AidStationId` = ?', $AidStationId);
	}
	//获取单个分站的补给代码列表
    public function getAidCodeByStage($params)
    {
        $oRace = new Xrace_Race();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStage($params['RaceStageId'],"RaceStageId,RaceCatalogId");
        if($RaceStageInfo['RaceStageId'])
        {
            //计算所在用户分表的后缀
            $suffix = $RaceStageInfo['RaceCatalogId'];
            //检测补给代码表是否存在
            $table_to_process = $this->db->createTable($this->table_aid_code,$suffix);
            if($table_to_process)
            {
                //用户
                $whereUser = isset($params['RaceUserId'])?" RaceUserId = ".$params['RaceUserId']:"";
                //获得分站
                $whereRaceStage = isset($params['RaceStageId'])?" RaceStageId = ".$params['RaceStageId']:"";
                //使用状态
                $whereUsed = isset($params['Used']) && ($params['Used'] != -1) ? " Used = '" . $params['Used']."'" : "";
                //使用状态
                $whereType = isset($params['AidCodeTypeId']) && ($params['AidCodeTypeId'] != 0) ? " AidCodeTypeId = " . $params['AidCodeTypeId']."" : "";
                //所有查询条件置入数组
                $whereCondition = array($whereUser,$whereRaceStage,$whereUsed,$whereType);
                //生成条件列
                $where = Base_common::getSqlWhere($whereCondition);
                $limit  = $params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
                $sql = "SELECT * FROM $table_to_process where 1 ".$where." order by AidCodeId desc".$limit;
                $return = $this->db->getAll($sql);
                if($params['GetCount']==0)
                {
                    return array("AidCodeList"=>$return);
                }
                else
                {
                    return array("AidCodeList"=>$return,"AidCodeCount"=>$this->getAidCodeCountByStage(array_merge($params,array("RaceCatalogId"=>$RaceStageInfo['RaceCatalogId']))));
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    //获取单个分站的补给代码列表
    public function getAidCodeCountByStage($params)
    {
        //计算所在用户分表的后缀
        $suffix = $params['RaceCatalogId'];
        //检测补给代码表是否存在
        $table_to_process = Base_Widget::getDbTable($this->table_aid_code) . "_" . $suffix;
        //用户
        $whereUser = isset($params['RaceUserId'])?" RaceUserId = ".$params['RaceUserId']:"";
        //获得分站
        $whereRaceStage = isset($params['RaceStageId'])?" RaceStageId = ".$params['RaceStageId']:"";
        //使用状态
        $whereUsed = isset($params['Used']) && ($params['Used'] != -1) ? " Used = '" . $params['Used']."'" : "";
        //使用状态
        $whereType = isset($params['AidCodeTypeId']) && ($params['AidCodeTypeId'] != 0) ? " AidCodeTypeId = " . $params['AidCodeTypeId']."" : "";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereRaceStage,$whereUsed,$whereType);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT count(1) as AidCodeCount FROM $table_to_process where 1 " . $where;
        $return = $this->db->getOne($sql);
        return $return;
    }
    //获取单个分站的补给代码列表
    public function genAidCode($RaceStageId,$AidCodeTypeId,$AidCodeCount)
    {
        $oRace = new Xrace_Race();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStage($RaceStageId,"RaceStageId,RaceCatalogId");
        if($RaceStageInfo['RaceStageId'])
        {
            $Success = 0;
            //计算所在用户分表的后缀
            $suffix = $RaceStageInfo['RaceCatalogId'];
            //检测补给代码表是否存在
            $table_to_process = $this->db->createTable($this->table_aid_code,$suffix);
            if($table_to_process)
            {
                for($i=1;$i<=$AidCodeCount;$i++)
                {
                    $time = time();
                    $AidCode = array("AidCode"=>md5($RaceStageId."|".$AidCodeTypeId."|".$time.".".rand(1,999)),"GenTime"=>date("Y-m-d H:i:s",$time),"RaceStageId"=>$RaceStageId,"AidCodeTypeId"=>$AidCodeTypeId,"Used"=>0);
                    $add = $this->insertAidCode($AidCode,$suffix);
                    if($add)
                    {
                        $Success++;
                    }
                }
                return $Success;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    //添加单个补给代码
    public function insertAidCode(array $bind,$suffix)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_aid_code) . "_" . $suffix;
        return $this->db->insert($table_to_process, $bind);
    }
    //添加单个补给代码分类
    public function insertAidCodeType(array $bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_aid_code_type);
        return $this->db->insert($table_to_process, $bind);
    }
    public function updateAidCode($AidCode,$RaceStageId,$bind)
    {
        $RaceStageId = intval($RaceStageId);
        $AidCode = trim($AidCode);
        $oRace = new Xrace_Race();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStage($RaceStageId,"RaceStageId,RaceCatalogId");
        //计算所在用户分表的后缀
        $suffix = $RaceStageInfo['RaceCatalogId'];
        //检测补给代码表是否存在
        $table_to_process = $this->db->createTable($this->table_aid_code,$suffix);
        return $this->db->update($table_to_process, $bind, '`AidCode` = ?', $AidCode);
    }
    //根据分组名称获取单个赛事组别的信息
    public function getAidCodeTypeByStage($RaceStageId, $fields = '*')
    {
        $RaceStageId = intval($RaceStageId);
        $table_to_process = Base_Widget::getDbTable($this->table_aid_code_type);
        $Return = $this->db->select($table_to_process, $fields, '`RaceStageId` = ?', $RaceStageId);
        $AidCodeTypeList = array();
        foreach($Return as $key => $value)
        {
            $AidCodeTypeList[$value['AidCodeTypeId']] = $value;
        }
        return $AidCodeTypeList;
    }
    //获取单个补给代码
    public function getAidCode($AidCode,$RaceStageId)
    {
        $RaceStageId = intval($RaceStageId);
        $AidCode = trim($AidCode);
        $oRace = new Xrace_Race();
        //获取分站信息
        $RaceStageInfo = $oRace->getRaceStage($RaceStageId,"RaceStageId,RaceCatalogId");
        //计算所在用户分表的后缀
        $suffix = $RaceStageInfo['RaceCatalogId'];
        //检测补给代码表是否存在
        $table_to_process = $this->db->createTable($this->table_aid_code,$suffix);
        return $this->db->selectRow($table_to_process, "*", '`RaceStageId` = ? and `AidCode` = ?', array($RaceStageId,$AidCode));
    }
    public function applyAidCodeToUser($RaceUserId,$AidCode,$RaceStageId)
    {
        //获取补给代码信息
        $AidCodeInfo = $this->getAidCode($AidCode,$RaceStageId);
        //如果找到
        if(isset($AidCodeInfo['AidCodeId']))
        {
            //如果已经分配给当前选手
            if($AidCodeInfo['RaceUserId']==$RaceUserId)
            {
                return true;
            }
            //如果未分配
            elseif($AidCodeInfo['RaceUserId']==0)
            {
                //获取当前时间
                $Time = date("Y-m-d H:i:s",time());
                $bind = array("RaceUserId"=>$RaceUserId,"ApplyTime"=>$Time);
                return $this->updateAidCode($AidCode,$RaceStageId,$bind);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}
