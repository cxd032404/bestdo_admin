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
        $whereCondition = array($whereCompany,$whereDepartment_1,$whereDepartment_2,$whereDepartment_3);
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
     * 获取用户数量
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
        $whereCondition = array($whereCompany,$whereDepartment_1,$whereDepartment_2,$whereDepartment_3);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取单条记录
     * @param integer $Steps_id
     * @param string $fields
     * @return array
     */
    public function getSteps($Steps_id, $fields = '*')
    {
        $Steps_id = intval($Steps_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`Steps_id` = ?', $Steps_id);
    }
    /**
     * 更新
     * @param integer $Steps_id
     * @param array $bind
     * @return boolean
     */
    public function updateSteps($Steps_id, array $bind)
    {
        $Steps_id = intval($Steps_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`Steps_id` = ?', $Steps_id);
    }
    /**
     * 插入
     * @param array $bind
     * @return boolean
     */
    public function insertSteps(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 删除
     * @param integer $Steps_id
     * @return boolean
     */
    public function deleteSteps($Steps_id)
    {
        $Steps_id = intval($Steps_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`Steps_id` = ?', $Steps_id);
    }

}
