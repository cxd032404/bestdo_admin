<?php
/**
 * 活动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Vote extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_vote';

    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getVoteList($params = [],$fields = "*")
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
	    $whereActivity = (isset($params['acitivity_id']) && $params['acitivity_id']>0)?" acitivity_id = ".$params['acitivity_id']:"";
        $whereActivitys = (isset($params['acitivity_ids']) && is_array($params['acitivity_ids']))?" acitivity_id in (".implode(",",$params['acitivity_id']).")":"";

        $whereSign = (isset($params['vote_sign']) && trim($params['vote_sign'])!="")?" vote_sign = '".$params['vote_sign']."'":"";
        $whereExclude = (isset($params['exclude_id']) && $params['exclude_id'])>0?" vote_id != ".$params['exclude_id']:"";
        $whereCondition = array($whereActivity,$whereSign,$whereExclude,$whereActivitys);
        $where = Base_common::getSqlWhere($whereCondition);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY vote_id ASC";
		$return = $this->db->getAll($sql);
		$VoteList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
                $VoteList[$value['vote_id']] = $value;
			}
		}
		return $VoteList;
	}
	/**
	 * 获取单条记录
	 * @param integer $vote_id
	 * @param string $fields
	 * @return array
	 */
	public function getVote($vote_id, $fields = '*')
	{
		$vote_id = intval($vote_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`vote_id` = ?', $vote_id);
	}
	/**
	 * 更新
	 * @param integer $vote_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateVote($vote_id, array $bind)
	{
		$vote_id = intval($vote_id);
		$bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`vote_id` = ?', $vote_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertVote(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $vote_id
	 * @return boolean
	 */
	public function deleteVote($vote_id)
	{
		$vote_id = intval($vote_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`vote_id` = ?', $vote_id);
	}

}
