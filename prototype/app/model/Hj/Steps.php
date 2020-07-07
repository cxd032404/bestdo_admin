<?php
/**
 * 俱乐部相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Steps extends Base_Widget
{
    //声明所用到的表
    protected $table = 'user_steps';

    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getStepsDetailList($params = [],$fields = "*")
    {
        $table_to_process = Base_Widget::getDbTable($this->table);
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereDepartment_1 = (isset($params['department_id_1']) && $params['department_id_1']>0)?" department_id_1 = ".$params['department_id_1']:"";
        $whereDepartment_2 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereDepartment_3 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereStartDate = (isset($params['start_date']) && strtotime($params['start_date'])>0)?" date >= '".$params['start_date']."'":"";
        $whereEndDate = (isset($params['end_date']) && strtotime($params['end_date'])>0)?" date <= '".$params['end_date']."'":"";
        $whereCondition = array($whereCompany,$whereDepartment_1,$whereDepartment_2,$whereDepartment_3,$whereStartDate,$whereEndDate);
        $where = Base_common::getSqlWhere($whereCondition);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $LogCount = $this->getStepsDetailCount($params);
        }
        else
        {
            $LogCount = 0;
        }

        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY log_id desc ".$limit;
        $return = $this->db->getAll($sql);
        $StepsDetailList = array("DetailList"=>[],"LogCount"=>$LogCount);
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $StepsDetailList['DetailList'][$value['log_id']] = $value;
            }
        }
        return $StepsDetailList;
    }
    /**
     * 获取记录数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getStepsDetailCount($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("LogCount"=>"count(log_id)"));
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereDepartment_1 = (isset($params['department_id_1']) && $params['department_id_1']>0)?" department_id_1 = ".$params['department_id_1']:"";
        $whereDepartment_2 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereDepartment_3 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereStartDate = (isset($params['start_date']) && strtotime($params['start_date'])>0)?" date >= '".$params['start_date']."'":"";
        $whereEndDate = (isset($params['end_date']) && strtotime($params['end_date'])>0)?" date <= '".$params['end_date']."'":"";
        $whereCondition = array($whereCompany,$whereDepartment_1,$whereDepartment_2,$whereDepartment_3,$whereStartDate,$whereEndDate);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getStepsStatList($params = [],$groupByField = "user_id")
    {
        $fields = ["totalStep"=>"sum(step)","userCount"=>"count(distinct(user_id))"];
        $fields[] = $groupByField;
        $table_to_process = Base_Widget::getDbTable($this->table);
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereDepartment_1 = (isset($params['department_id_1']) && $params['department_id_1']>0)?" department_id_1 = ".$params['department_id_1']:"";
        $whereDepartment_2 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereDepartment_3 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereStartDate = (isset($params['start_date']) && strtotime($params['start_date'])>0)?" date >= '".$params['start_date']."'":"";
        $whereEndDate = (isset($params['end_date']) && strtotime($params['end_date'])>0)?" date <= '".$params['end_date']."'":"";
        $whereCondition = array($whereCompany,$whereDepartment_1,$whereDepartment_2,$whereDepartment_3,$whereStartDate,$whereEndDate);
        $where = Base_common::getSqlWhere($whereCondition);
        $groupBy = Base_Common::getGroupBy([$groupByField]);
        $fields = Base_Common::getSqlFields($fields);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $LogCount = $this->getStepsStatCount($params,$groupByField);
        }
        else
        {
            $LogCount = 0;
        }
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where.$groupBy." ORDER BY totalStep desc ".$limit;
        $return = $this->db->getAll($sql);
        $StepsDetailList = array("List"=>[],"Count"=>$LogCount);
        if(count($return))
        {
            foreach($return as $key => $value)
            {
                $StepsDetailList['List'][$value[$groupByField]] = $value;
            }
        }
        return $StepsDetailList;
    }
    /**
     * 获取记录数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getStepsStatCount($params,$groupByField = "user_id")
    {
        $fields = ["UserCount"=>"count(distinct(".$groupByField."))"];
        //$fields[] = $groupByField;
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields($fields);
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereDepartment_1 = (isset($params['department_id_1']) && $params['department_id_1']>0)?" department_id_1 = ".$params['department_id_1']:"";
        $whereDepartment_2 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereDepartment_3 = (isset($params['department_id_2']) && $params['department_id_2']>0)?" department_id_2 = ".$params['department_id_2']:"";
        $whereStartDate = (isset($params['start_date']) && strtotime($params['start_date'])>0)?" date >= '".$params['start_date']."'":"";
        $whereEndDate = (isset($params['end_date']) && strtotime($params['end_date'])>0)?" date <= '".$params['end_date']."'":"";
        $whereCondition = array($whereCompany,$whereDepartment_1,$whereDepartment_2,$whereDepartment_3,$whereStartDate,$whereEndDate);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $groupBy = Base_Common::getGroupBy($groupByField);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groupBy;
        return $this->db->getOne($sql);
    }

    public function setUserDepartment($user_id,$department)
    {
        $user_id = intval($user_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $department, '`user_id` = ?', $user_id);
    }
}
