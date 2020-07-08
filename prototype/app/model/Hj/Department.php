<?php
/**
 * 部门相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Department extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_department';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getDepartmentList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
        $wherePermission = isset($params['permissionList'])?(
        count($params['permissionList'])>0?
            ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
            :" 0 "):"";
		$whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereName = (isset($params['department_name']) && trim($params['department_name'])!="")?" department_name = '".$params['department_name']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" department_id != ".$params['exclude_id']:"";
        $whereParent = isset($params['parent_id'])?" parent_id = ".$params['parent_id']:"";
        $whereCondition = array($wherePermission,$whereCompany,$whereExclude,$whereParent,$whereName);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY department_id ASC";
		$return = $this->db->getAll($sql);
		$DepartmentList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$DepartmentList[$value['department_id']] = $value;
			}
		}
		return $DepartmentList;
	}
    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getDepartmentCount($params = [],$fields = "*")
    {
        //生成查询列
        $fields = Base_common::getSqlFields(array("DepartmentCount"=>"count(department_id)"));
        $table_to_process = Base_Widget::getDbTable($this->table);
        $wherePermission = isset($params['permissionList'])?(
        count($params['permissionList'])>0?
            ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
            :" 0 "):"";
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereName = (isset($params['department_name']) && trim($params['department_name'])!="")?" department_name = '".$params['department_name']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" department_id != ".$params['exclude_id']:"";
        $whereParent = isset($params['parent_id'])?" parent_id = ".$params['parent_id']:"";
        $whereCondition = array($wherePermission,$whereCompany,$whereExclude,$whereParent,$whereName);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY department_id ASC";
        return $this->db->getOne($sql);
    }
	/**
	 * 获取单条记录
	 * @param integer $department_id
	 * @param string $fields
	 * @return array
	 */
	public function getDepartment($department_id, $fields = '*')
	{
		$department_id = intval($department_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`department_id` = ?', $department_id);
	}
    /**
     * 根据部门名称获取单条记录
     * @param integer $company_id
     * @param string $department_name
     * @param string $fields
     * @return array
     */
    public function getDepartmentByName($company_id,$department_name, $fields = '*')
    {
        $company_id = intval($company_id);
        $department_name = trim($department_name);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`company_id` = ? and `department_name` = ?', [$company_id,$department_name]);
    }
	/**
	 * 更新
	 * @param integer $department_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateDepartment($department_id, array $bind)
	{
		$department_id = intval($department_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`department_id` = ?', $department_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertDepartment(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $department_id
	 * @return boolean
	 */
	public function deleteDepartment($department_id)
	{
		$department_id = intval($department_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`department_id` = ?', $department_id);
	}
}
