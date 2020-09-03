<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race_Team extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_team';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getTeamList($params,$fields = "*")
	{
        $whereRace = (isset($params['race_id']) && $params['race_id']>0)?" race_id = ".$params['race_id']:"";
        //初始化查询条件
		$whereCondition = array($whereRace);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY team_id ASC";
        $return = $this->db->getAll($sql);
		$TeamList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$TeamList[$value['team_id']] = $value;
			}
		}
		return $TeamList;
	}
	/**
	 * 获取单条记录
	 * @param integer $team_id
	 * @param string $fields
	 * @return array
	 */
	public function getTeam($team_id, $fields = '*')
	{
		$team_id = intval($team_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`team_id` = ?', $team_id);
	}
	/**
	 * 更新
	 * @param integer $team_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateTeam($team_id, array $bind)
	{
		$team_id = intval($team_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`team_id` = ?', $team_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertTeam(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $team_id
	 * @return boolean
	 */
	public function deleteTeam($team_id)
	{
		$team_id = intval($team_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`team_id` = ?', $team_id);
	}
}
