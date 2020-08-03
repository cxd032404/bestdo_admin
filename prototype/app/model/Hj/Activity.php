<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Activity extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_activity';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getActivityList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
        $wherePermission = isset($params['permissionList'])?(
        count($params['permissionList'])>0?
            ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
            :" 0 "):"";
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereSign = (isset($params['activity_sign']) && trim($params['activity_sign'])!="")?" activity_sign = '".$params['activity_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" activity_id != ".$params['exclude_id']:"";
        $whereSystem = (isset($params['system']) && $params['system']>=0)?" system = ".$params['system']:"";
        $wherePurchased = (isset($params['purchased']) && $params['purchased']>=0)?" purchased = ".$params['purchased']:"";
        $whereCondition = array($wherePermission,$whereCompany,$whereSign,$whereExclude,$whereSystem,$wherePurchased);
        $where = Base_common::getSqlWhere($whereCondition);
        $limit  = isset($params['Page'])&&$params['Page']?" limit ".($params['Page']-1)*$params['PageSize'].",".$params['PageSize']." ":"";
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY activity_id ASC ".$limit;

        $return = $this->db->getAll($sql);
        //获取用户数量
        if(isset($params['getCount'])&&$params['getCount']==1)
        {
            $activityCount = $this->getActivityCount($params);
        }
        else
        {
            $activityCount = 0;
        }
        $ActivityList = array('ActivityList'=>array(),'ActivityCount'=>$activityCount);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $ActivityList['ActivityList'][$value['activity_id']] = $value;
			}
		}
        return $ActivityList;
	}
    /**
     * 查询全部
     * @param $fields
     * @return array
     */
    public function getActivityCount($params = [])
    {
        $fields = Base_common::getSqlFields(array("ActivityCount"=>"count(activity_id)"));
        $table_to_process = Base_Widget::getDbTable($this->table);
        $wherePermission = isset($params['permissionList'])?(
        count($params['permissionList'])>0?
            ( " company_id in (".implode(",",array_column($params['permissionList'],"company_id")).") ")
            :" 0 "):"";
        $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereSign = (isset($params['activity_sign']) && trim($params['activity_sign'])!="")?" activity_sign = '".$params['activity_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" activity_id != ".$params['exclude_id']:"";
        $whereSystem = (isset($params['system']) && $params['system']>=0)?" system = ".$params['system']:"";
        $wherePurchased = (isset($params['purchased']) && $params['purchased']>=0)?" purchased = ".$params['purchased']:"";
        $whereCondition = array($wherePermission,$whereCompany,$whereSign,$whereExclude,$whereSystem,$wherePurchased);        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM $table_to_process where 1 ".$where;
        return $this->db->getOne($sql);
    }
	/**
	 * 获取单条记录
	 * @param integer $activity_id
	 * @param string $fields
	 * @return array
	 */
	public function getActivity($activity_id, $fields = '*')
	{
		$activity_id = intval($activity_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`activity_id` = ?', $activity_id);
	}
	/**
	 * 更新
	 * @param integer $activity_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateActivity($activity_id, array $bind)
	{
		$activity_id = intval($activity_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`activity_id` = ?', $activity_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertActivity(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $activity_id
	 * @return boolean
	 */
	public function deleteActivity($activity_id)
	{
		$activity_id = intval($activity_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`activity_id` = ?', $activity_id);
	}

}
