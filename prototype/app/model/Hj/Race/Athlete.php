<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race_Athlete extends Base_Widget
{
	//声明所用到的表
	protected $table = 'user_athlete';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getAthleteList($params,$fields = "*")
	{
        $whereRace = (isset($params['race_id']) && $params['race_id']>0)?" race_id = ".$params['race_id']:"";
        //初始化查询条件
		$whereCondition = array($whereRace);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY group_id,seed,athlete_id desc";
        $return = $this->db->getAll($sql);
		$AthleteList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$AthleteList[$value['athlete_id']] = $value;
			}
		}
		return $AthleteList;
	}
	/**
	 * 获取单条记录
	 * @param integer $athlete_id
	 * @param string $fields
	 * @return array
	 */
	public function getAthlete($athlete_id, $fields = '*')
	{
		$athlete_id = intval($athlete_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`athlete_id` = ?', $athlete_id);
	}
	/**
	 * 更新
	 * @param integer $athlete_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateAthlete($athlete_id, array $bind)
	{
		$athlete_id = intval($athlete_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`athlete_id` = ?', $athlete_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertAthlete(array $bind)
	{
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $athlete_id
	 * @return boolean
	 */
	public function deleteAthlete($athlete_id)
	{
		$athlete_id = intval($athlete_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`athlete_id` = ?', $athlete_id);
	}
}
