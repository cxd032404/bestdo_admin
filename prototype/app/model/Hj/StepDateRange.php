<?php
/**
 * 企业相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_StepDateRange extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_steps_date_range';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getDateList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
        //企业
        $whereCompany = (isset($params['company_id']) && intval($params['company_id'])>0)?" company_id = ".$params['company_id']." ":"";
        //排除的ID
        $whereExclude = (isset($params['exclude_id'])&& $params['exclude_id']>0)?" date_id != ".$params['date_id']:"";
        $whereCondition = array($whereCompany,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY date_id DESC ".$limit;
        //获取记录数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $DateCount = $this->getDateCount($params);
        }
        else
        {
            $DateCount = 0;
        }
        $DateList = array("DateList"=>[],"DateCount"=>$DateCount);
        $return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $DateList['DateList'][$value['date_id']] = $value;
			}
		}
		return $DateList;
	}
    /**
     * 获取用户数量
     * @param $fields  所要获取的数据列
     * @param $params 传入的条件列表
     * @return integer
     */
    public function getDateCount($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("DateCount"=>"count(date_id)"));
        //企业
        $whereCompany = (isset($params['company_id']) && intval($params['company_id'])>0)?" company_id = ".$params['company_id']." ":"";
        //排除的ID
        $whereExclude = (isset($params['exclude_id'])&& $params['exclude_id']>0)?" date_id != ".$params['date_id']:"";
        $whereCondition = array($whereCompany,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        //生成条件列
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }

    public function checkDateExist($params)
    {
        //获取需要用到的表名
        $table_to_process = Base_Widget::getDbTable($this->table);
        //生成查询列
        $fields = Base_common::getSqlFields(array("DateCount"=>"count(date_id)"));
        //生成条件列
        $where = " company_id = ".$params['company_id']." and ((start_date <= '".$params['start_date']."' and end_date >= '".$params['start_date']."') or (start_date <= '".$params['end_date']."' and end_date >= '".$params['start_date']."'))";
        if(isset($params['date_id']))
        {
            $where .= " and date_id != ".$params['date_id'];
        }
        $sql = "SELECT $fields FROM $table_to_process where ".$where;
        return $this->db->getOne($sql);
    }
    /**
     * 获取单条记录
     * @param integer $date_id
     * @param string $fields
     * @return array
     */
    public function getDateRange($date_id, $fields = '*')
    {
        $date_id = intval($date_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`date_id` = ?', $date_id);
    }
    /**
     * 插入
     * @param array $bind
     * @return boolean
     */
    public function insertDateRange(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }
    /**
     * 更新
     * @param integer $company_id
     * @param array $bind
     * @return boolean
     */
    public function updateDateRange($date_id, array $bind)
    {
        $date_id = intval($date_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->update($table_to_process, $bind, '`date_id` = ?', $date_id);
    }
    /**
     * 删除
     * @param integer $date_id
     * @return boolean
     */
    public function deleteDateRange($date_id)
    {
        $date_id = intval($date_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->delete($table_to_process, '`date_id` = ?', $date_id);
    }

}
