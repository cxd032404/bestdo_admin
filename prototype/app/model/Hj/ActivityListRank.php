<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_ActivityListRank extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_activity_list_rank';

	/*
	 * 查询
	 */
    public function getActivityRankLog($params = [],$fields = 'log_id,post_count,kudos_count'){
        $where = '';
        foreach ($params as $key=>$value)
        {
            $where .=' and '.$key.'='.$value;
        }
        $where = substr($where,4);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, $where);
    }

	/**
	 * 更新
	 * @param integer $activity_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateActivityRankLog($params, array $bind)
	{
        $where = '';
        foreach ($params as $key=>$value)
        {
            $where .=' and '.$key.'='.$value;
        }
        $where = substr($where,4);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, $where);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertActivityRankLog(array $bind)
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
