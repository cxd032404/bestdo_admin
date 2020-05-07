<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_List extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_list';
    protected $list_type = array("text"=>"文本","pic"=>"图片","video"=>"视频",
        "rankingFromKudo"=>"点赞排名",'rankingFromUpload'=>"用户上传排名");
    //获取列表类型
    public function getListType()
    {
        return $this->list_type;
    }
    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getListList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
	    $whereCompany = (isset($params['company_id']) && $params['company_id']>0)?" company_id = ".$params['company_id']:"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" list_id != ".$params['exclude_id']:"";
        $whereCondition = array($whereCompany,$whereExclude);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY activity_id ASC";
		$return = $this->db->getAll($sql);
		$ActivityList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $ActivityList[$value['activity_id']] = $value;
			}
		}
		return $ActivityList;
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
