<?php
/**
 * 运动相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_Race extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_race';
    //速度显示规则列表
    protected $raceTypeList =
        [
            'cup'=>'杯赛',
            'league'=>'联赛',
            'single'=>'单独比赛'
        ];

    public function getRaceTypeList()
    {
        return $this->raceTypeList;
    }
    /**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getRaceList($params = [],$fields = "*")
	{
        $table_to_process = Base_Widget::getDbTable($this->table);
        $whereType = (isset($params['race_type']) && trim($params['race_type'])!="")?" race_type = '".$params['race_type']."'":"";
        $whereCondition = array($whereType);
        $where = Base_common::getSqlWhere($whereCondition);
        $sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY race_id DESC";
        $return = $this->db->getAll($sql);
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$RaceList[$value['race_id']] = $value;
			}
		}
		return $RaceList;
	}
	/**
	 * 获取单条记录
	 * @param integer $race_id
	 * @param string $fields
	 * @return array
	 */
	public function getRace($race_id, $fields = '*')
	{
		$race_id = intval($race_id);
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`race_id` = ?', $race_id);
	}
	/**
	 * 更新
	 * @param integer $race_id
	 * @param array $bind
	 * @return boolean
	 */
	public function updateRace($race_id, array $bind)
	{
		$race_id = intval($race_id);
        $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`race_id` = ?', $race_id);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertRace(array $bind)
	{
		$bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
	    $table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $race_id
	 * @return boolean
	 */
	public function deleteRace($race_id)
	{
		$race_id = intval($race_id);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`race_id` = ?', $race_id);
	}

}
