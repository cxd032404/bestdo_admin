<?php
/**
 * 俱乐部成员相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_ClubMember extends Base_Widget
{
    //声明所用到的表
    protected $table = 'club_member';
    protected $table_log = 'club_member_log';


    /**
     * 插入俱乐部成员
     * @param array $bind
     * @return boolean
     */
    public function insertClubMember(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 插入俱乐部成员日志
     * @param array $bind
     * @return boolean
     */
    public function insertClubMemberLog(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = $bind['process_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table_log);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 获取用户列表
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getMemberList($params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        $order = " ORDER BY member_id desc";
        $whereUser = (isset($params['user_id']) && ($params['user_id']>0))?" user_id = '".$params['user_id']."' ":"";
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //俱乐部
        $whereClub = (isset($params['club_id']) && ($params['club_id']>0))?" club_id = '".$params['club_id']."' ":"";
        //状态
        $whereStatus = (isset($params['status']))?" status = '".$params['status']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereCompany,$whereClub,$whereStatus);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $MemberCount = $this->getMemberCount($params);
        }
        else
        {
            $MemberCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
        $return = $this->db->getAll($sql);
        $MemberList = array('MemberList'=>array(),'MemberCount'=>$MemberCount);
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $MemberList['MemberList'][$value['member_id']] = $value;
            }
        }
        else
        {
            //return $MemberList;
        }
        return $MemberList;
    }
    /**
     * 获取用户数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getMemberCount($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("MemberCount"=>"count(member_id)"));
        $whereUser = (isset($params['user_id']) && ($params['user_id']>0))?" user_id = '".$params['user_id']."' ":"";
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //俱乐部
        $whereClub = (isset($params['club_id']) && ($params['club_id']>0))?" club_id = '".$params['club_id']."' ":"";
        //状态
        $whereStatus = (isset($params['status']))?" status = '".$params['status']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereUser,$whereCompany,$whereClub,$whereStatus);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取用户列表
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return array
     */
    public function getMemberLogList($params,$fields = array("*"))
    {
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_log);
        $order = " ORDER BY update_time desc";
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //俱乐部
        $whereClub = (isset($params['club_id']) && ($params['club_id']>0))?" club_id = '".$params['club_id']."' ":"";
        //结果
        $whereResult = (isset($params['result']))?" result = '".$params['result']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$whereClub,$whereResult);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $Logcount = $this->getMemberLogCount($params);
        }
        else
        {
            $MemberCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where." ".$order." ".$limit;
        $return = $this->db->getAll($sql);
        $MemberList = array('LogList'=>array(),'LogCount'=>$Logcount);
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $MemberList['LogList'][$value['log_id']] = $value;
            }
        }
        else
        {
            //return $MemberList;
        }
        return $MemberList;
    }
    /**
     * 获取用户数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getMemberLogCount($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table_log);
        //生成查询列
        $fields = Base_common::getSqlFields(array("MemberCount"=>"count(log_id)"));
        //企业
        $whereCompany = (isset($params['company_id']) && ($params['company_id']>0))?" company_id = '".$params['company_id']."' ":"";
        //俱乐部
        $whereClub = (isset($params['club_id']) && ($params['club_id']>0))?" club_id = '".$params['club_id']."' ":"";
        //结果
        $whereResult = (isset($params['result']))?" result = '".$params['result']."' ":"";
        //所有查询条件置入数组
        $whereCondition = array($whereCompany,$whereClub,$whereResult);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }

    public function processMemberAction($type,$subType)
    {
        if($type==1)
        {
            if($subType==1)
            {
                $actionName = "主动申请加入";
            }
            else
            {
                $actionName = "邀请加入";
            }
        }
        else
        {
            if($subType==1)
            {
                $actionName = "主动离开";
            }
            else
            {
                $actionName = "踢出";
            }
        }
        return $actionName;
    }
    public function processMemberLogResult($user_id,$operate_user_id,$process_user_id,$result)
    {
        if($result == 0)
        {
            //本人操作
            if($user_id == $operate_user_id)
            {
                $resultName = "等待审核";
            }
            else
            {
                $resultName = "等待通过";
            }
        }
        elseif($result == 1)
        {
            //本人操作
            if($user_id == $process_user_id)
            {
                $resultName = "通过";
            }
            else
            {
                $resultName = "通过";
            }
        }
        elseif($result == 2)
        {
            //本人操作
            if($user_id == $process_user_id)
            {
                $resultName = "拒绝";
            }
            else
            {
                $resultName = "拒绝";
            }
        }
        return $resultName;
    }

}
