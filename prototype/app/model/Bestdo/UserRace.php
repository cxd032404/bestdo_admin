<?php
/**
 * 赛事配置相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_UserRace extends Base_Widget
{
	//声明所用到的表
	protected $table = 'RaceApplyQueue';
    protected $table_race = 'AppliedRaceList';
    protected $table_user_race = 'UserRaceList';
	protected $maxRaceAppplyCount = 5;
	protected $raceStausList = array("1"=>"未比赛","2"=>"已结束","3"=>"已取消");
    protected $userRaceStausList = array("0"=>"未比赛","1"=>"胜利","2"=>"失败","3"=>"平局");

    public function getMaxRaceAppplyCount()
    {
        return $this->maxRaceAppplyCount;
    }

    public function getRaceStausList()
    {
        return $this->raceStausList;
    }

    public function getUserRaceStausList()
    {
        return $this->userRaceStausList;
    }
    /**
     * 新增约战
     * @param array $bind
     * @return boolean
     */
	public function insertRaceApply($bind)
	{
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
	}
    /**
     * 新增约战
     * @param array $bind
     * @return boolean
     */
    public function deleteRaceApply($ApplyId)
    {
        $ApplyId = intval($ApplyId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`ApplyId` = ?', $ApplyId);
    }
    /**
     * 新增对战比赛
     * @param array $bind
     * @return boolean
     */
    public function insertAppliedRace($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 更新用户比赛记录
     * @param int $RaceId 比赛ID
     * @return boolean
     */
    public function updateUserAppliedRace($RaceId, array $bind)
    {
        $RaceId = intval($RaceId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->update($table_to_process, $bind, '`RaceId` = ?', $RaceId);
    }
    /**
     * 新增用户比赛记录
     * @param array $bind
     * @return boolean
     */
    public function insertUserRace($bind)
    {
        $table_to_process = Base_Widget::getDbTable($this->table_user_race);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 获取用户比赛记录
     * @param int $RaceId 比赛ID
     * @return boolean
     */
    public function getAppliedRace($RaceId, $fields = '*')
    {
        $RaceId = intval($RaceId);
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        return $this->db->selectRow($table_to_process, $fields, '`RaceId` = ?', $RaceId);
    }
    /**
     * 更新用户比赛记录
     * @param int $RaceId 比赛ID
     * @return boolean
     */
    public function updateUserRace($UserRaceId, array $bind)
    {
        $UserRaceId = intval($UserRaceId);
        $table_to_process = Base_Widget::getDbTable($this->table_user_race);
        return $this->db->update($table_to_process, $bind, '`UserRaceId` = ?', $UserRaceId);
    }
    /**
     * 获得约战信息
     * @param array $bind
     * @param char $ApplyId 约战记录ID
     * @return boolean
     */
    public function getUserRaceApply($ApplyId, $fields = '*')
    {
        $ApplyId = intval($ApplyId);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`ApplyId` = ?', $ApplyId);
    }

    //获取报名各状态约战记录的列表
    public function getUserRaceApplyList($params,$fields = array('*'))
    {
        $ReturnArr = array();
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //获得用户ID
        $whereUser = (isset($params['UserId']) && $params['UserId']!="0")?" UserId = '".$params['UserId']."' ":"";
        //获得要忽略的用户ID
        $whereUserIgnore = (isset($params['UserIgnore']) && $params['UserIgnore']!="0")?" UserId != '".$params['UserIgnore']."' ":"";
        //获得场地ID
        $whereArena = (isset($params['ArenaId'])  && $params['ArenaId']!=0)?" ArenaId = '".$params['ArenaId']."' ":"";
        //获得芯片ID
        $whereChip = (isset($params['ChipId']) && strlen($params['ChipId'])>4)?" ChipId = '".$params['ChipId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereArena,$whereChip,$whereUserIgnore);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by ApplyId desc".$limit;
        $return = $this->db->getAll($sql);
        foreach($return as $key => $ApplyInfo)
        {
            $ReturnArr["UserRaceApplyList"][$ApplyInfo['ApplyId']] = $ApplyInfo;
        }
        //获取记录数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $ReturnArr['UserRaceApplyCount'] = $this->getUserRaceApplyCount($params);
        }
        else
        {
            $ReturnArr['UserRaceApplyCount'] = 0;
        }
        return $ReturnArr;
    }
    //获取报名各状态约战记录的数量
    public function getUserRaceApplyCount($params)
    {
        $ReturnArr = array();
        //生成查询列
        $fields = Base_common::getSqlFields(array("ApplyCount"=>"count(1)"));
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //获得用户ID
        $whereUser = (isset($params['UserId']) && $params['UserId']!="0")?" UserId = '".$params['UserId']."' ":"";
        //获得要忽略的用户ID
        $whereUserIgnore = (isset($params['UserIgnore']) && $params['UserIgnore']!="0")?" UserId != '".$params['UserIgnore']."' ":"";
        //获得场地ID
        $whereArena = (isset($params['ArenaId'])  && $params['ArenaId']!=0)?" ArenaId = '".$params['ArenaId']."' ":"";
        //获得芯片ID
        $whereChip = (isset($params['ChipId']) && strlen($params['ChipId'])>4)?" ChipId = '".$params['ChipId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereArena,$whereChip,$whereUserIgnore);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 用户直接加入某个约战
     * @param char $ApplyId 约战记录ID
     * @param int $Individual 是否单人对战
     * @param array $UserList 双方用户列表
     * @return boolean
     */
    public function insertUserAppliedRace($ApplyId,$Individual = 1,$UserList)
    {
        //获取约战信息
        $ApplyInfo = $this->getUserRaceApply($ApplyId);
        {
            //初始化比赛信息
            $RaceInfo = array("ApplyUserId"=>$ApplyInfo['UserId'],"RaceCreateTime"=>time(),"RaceStartTime"=>$ApplyInfo["ApplyStartTime"],"RaceEndTime"=>$ApplyInfo['ApplyEndTime'],"ArenaId"=>$ApplyInfo['ArenaId'],"Individual"=>$Individual);
            $this->db->begin();
            //新建比赛
            $RaceId = $this->insertAppliedRace($RaceInfo);
            //如果是单人对战
            if($Individual == 1)
            {
                //如果比赛创建成功
                if($RaceId)
                {
                    foreach($UserList as $key => $UserInfo)
                    {
                        //初始化用户参赛信息
                        $UserRaceInfo = array("RaceId"=>$RaceId,"UserId"=>$UserInfo['UserId'],"TeamId"=>0,"Result"=>0,"ApplyId"=>($ApplyInfo['UserId']==$UserInfo['UserId']?$ApplyId:0),"ChipId"=>$UserInfo['ChipId']);
                        //新建用户参赛信息
                        $UserRace = $this->insertUserRace($UserRaceInfo);
                        if(!$UserRace)
                        {
                            //事务回滚
                            $this->db->rollBack();
                            return false;
                        }
                    }
                    //删除原有的约战记录
                    $deleteApp = $this->deleteRaceApply($ApplyId);
                    if($deleteApp)
                    {
                        //事务提交
                        $this->db->commit();
                        return $RaceId;
                    }
                    else
                    {
                        //事务回滚
                        $this->db->rollBack();
                        return false;
                    }
                }
            }
            else
            {

            }
        }
    }
    //获取报名各状态对战记录的列表
    public function getUserRaceList($params,$fields = array('*'))
    {
        $ReturnArr = array();
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_user_race);
        //获得用户ID
        $whereUser = (isset($params['UserId']) && $params['UserId']!="0")?" UserId = '".$params['UserId']."' ":"";
        //获得比赛ID
        $whereRace = (isset($params['RaceId']) && $params['RaceId']!="0")?" RaceId = '".$params['RaceId']."' ":"";
        //获得要忽略的用户ID
        $whereUserIgnore = (isset($params['UserIgnore']) && $params['UserIgnore']!="0")?" UserId != '".$params['UserIgnore']."' ":"";
        //获得场地ID
        $whereArena = (isset($params['ArenaId'])  && $params['ArenaId']!=0)?" ArenaId = '".$params['ArenaId']."' ":"";
        //获得芯片ID
        $whereChip = (isset($params['ChipId']) && strlen($params['ChipId'])>4)?" ChipId = '".$params['ChipId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereRace,$whereUserIgnore,$whereArena,$whereChip);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by UserRaceId desc".$limit;
        $return = $this->db->getAll($sql);
        foreach($return as $key => $ApplyInfo)
        {
            $ReturnArr["UserRaceList"][$ApplyInfo['UserRaceId']] = $ApplyInfo;
        }
        //获取记录数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $ReturnArr['UserRaceCount'] = $this->getUserRaceCount($params);
        }
        else
        {
            $ReturnArr['UserRaceCount'] = 0;
        }
        return $ReturnArr;
    }
    //获取报名各状态对战记录的列表
    public function getUserRaceCount($params)
    {
        //生成查询列
        $fields = Base_common::getSqlFields(array("RaceCount"=>"count(1)"));
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_user_race);
        //获得用户ID
        $whereUser = (isset($params['UserId']) && $params['UserId']!="0")?" UserId = '".$params['UserId']."' ":"";
        //获得比赛ID
        $whereRace = (isset($params['RaceId']) && $params['RaceId']!="0")?" RaceId = '".$params['RaceId']."' ":"";
        //获得要忽略的用户ID
        $whereUserIgnore = (isset($params['UserIgnore']) && $params['UserIgnore']!="0")?" UserId != '".$params['UserIgnore']."' ":"";
        //获得场地ID
        $whereArena = (isset($params['ArenaId'])  && $params['ArenaId']!=0)?" ArenaId = '".$params['ArenaId']."' ":"";
        //获得芯片ID
        $whereChip = (isset($params['ChipId']) && strlen($params['ChipId'])>4)?" ChipId = '".$params['ChipId']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereRace,$whereUserIgnore,$whereArena,$whereChip);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    //获取报名各状态对战记录的列表
    public function getUserAppliedRaceList($params,$fields = array('*'))
    {
        $ReturnArr = array();
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereRace = (isset($params['RaceId']) && $params['RaceId']!="0")?" RaceId = '".$params['RaceId']."' ":"";
        //获得场地ID
        $whereArena = (isset($params['ArenaId'])  && $params['ArenaId']!=0)?" ArenaId = '".$params['ArenaId']."' ":"";
        //获得比赛状态
        $whereRaceStatus = (isset($params['RaceStatus'])  && $params['RaceStatus']!=0)?" RaceStatus = '".$params['RaceStatus']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereRace,$whereArena,$whereRaceStatus);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //分页参数
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." order by RaceId desc".$limit;
        $return = $this->db->getAll($sql);
        foreach($return as $key => $ApplyInfo)
        {
            $ReturnArr["UserRaceList"][$ApplyInfo['RaceId']] = $ApplyInfo;
        }
        //获取记录数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $ReturnArr['UserRaceCount'] = $this->getUserAppliedRaceCount($params);
        }
        else
        {
            $ReturnArr['UserRaceCount'] = 0;
        }
        return $ReturnArr;
    }
    //获取报名各状态对战记录的列表
    public function getUserAppliedRaceCount($params)
    {
        //生成查询列
        $fields = Base_common::getSqlFields(array("RaceCount"=>"count(1)"));
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_race);
        //获得比赛ID
        $whereRace = (isset($params['RaceId']) && $params['RaceId']!="0")?" RaceId = '".$params['RaceId']."' ":"";
        //获得场地ID
        $whereArena = (isset($params['ArenaId'])  && $params['ArenaId']!=0)?" ArenaId = '".$params['ArenaId']."' ":"";
        //获得比赛状态
        $whereRaceStatus = (isset($params['RaceStatus'])  && $params['RaceStatus']!=0)?" RaceStatus = '".$params['RaceStatus']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereRace,$whereArena,$whereRaceStatus);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    //获取报名各状态对战记录的列表
    public function updateAppliedUserRaceResult($params)
    {
        //获取比赛信息
        $RaceInfo = $this->getAppliedRace($params['RaceId']);
        //如果获取到
        if($RaceInfo['RaceId'])
        {
            //如果当前状态为未比赛
            if(($RaceInfo['RaceStatus']==1) || ($params['Force']==1))
            {
                $this->db->begin();
                //数据解包
                $RaceInfo['comment'] = json_decode($RaceInfo['comment'],true);
                //更新时间
                $RaceInfo['comment']['Result']['UpdateTime'] = time();
                //管理员
                $RaceInfo['comment']['Result']['manager'] = $params['manager'];
                //比赛状态
                $RaceInfo['RaceStatus'] = 2;
                //数据压缩
                $RaceInfo['comment'] = json_encode($RaceInfo['comment']);
                //如果是单人比赛
                if($RaceInfo['Individual']==1)
                {
                    //获取对战的用户列表
                    $UserList = $this->getUserRaceList(array("RaceId" => $RaceInfo['RaceId'],"getCount"=>0));
                    //如果有指定赢家
                    if(isset($params['WinnerUser']))
                    {
                        //如果为指定特定赢家（平局）
                        if($params['WinnerUser']==0)
                        {
                            //循环用户列表
                            foreach($UserList['UserRaceList'] as $key => $UserInfo)
                            {
                                //判定为平局
                                $bind = array("Result"=>3);
                                //更新比赛记录
                                $update = $this->updateUserRace($UserInfo['UserRaceId'],$bind);
                                //如果更新失败
                                if(!$update)
                                {
                                    //回滚
                                    $this->db->rollBack();
                                    return false;
                                }
                            }
                        }
                        //如果指定特定赢家
                        elseif($params['WinnerUser']>0)
                        {
                            //循环用户列表
                            foreach($UserList['UserRaceList'] as $key => $UserInfo)
                            {
                                //如果是赢家
                                if($UserInfo['UserId']==$params['WinnerUser'])
                                {
                                    //判定为胜利
                                    $bind = array("Result"=>1);
                                }
                                else
                                {
                                    //判定为失败
                                    $bind = array("Result"=>2);
                                }
                                //更新比赛记录
                                $update = $this->updateUserRace($UserInfo['UserRaceId'],$bind);
                                //如果更新失败
                                if(!$update)
                                {
                                    //回滚
                                    $this->db->rollBack();
                                    return false;
                                }
                            }
                        }
                    }
                }
                //更新比赛记录
                $update = $this->updateUserAppliedRace($RaceInfo['RaceId'],$RaceInfo);
                //如果更新成功
                if($update)
                {
                    //事务提交
                    $this->db->commit();
                    $oAction = new Xrace_Action();
                    $CreditList = $oAction->CreditByAction("rc_daily_race",$params['WinnerUser'],array("UserRaceId"=>$params['RaceId']));
                    return true;
                }
                else
                {
                    //回滚
                    $this->db->rollBack();
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
}
